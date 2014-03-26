class UsersController < ApplicationController 
  before_filter :only => [:index, :new, :delete, :racechange, :namechange, :clanchange] do 
    @std_params = params.reject {|key,v| !["action", "world", "order", "by", "name"].include?(key)}
       
    params[:page] = params[:page].to_i
    
    @limit = 20
    @suggest_limit = 5
    @offset = (params[:page]  - 1) * @limit
    
    params[:by] ||= 'desc'
    if params[:by].to_s.downcase.eql?('desc') # define sorting order for sort_links
      @by = 'asc'
    else
      @by = 'desc'
    end
    
    @worlds = @worlds_all = World.includes(:language).order("id asc")
    
    unless params[:world].nil?
      @worlds = @worlds.where(:short => params[:world])
    end
    
    # Rassen
    @races = Race.order("name ASC")
    
    # es wird nach einer Rasse gesucht
    unless params[:race].nil?
      @race = @races.where("name = ? OR id = ? OR short = ?", 
                           params[:race], params[:race], params[:race]).first
    end
    
    # Rasse wechseln per default nicht möglich
    @change_race = false
    
    @last_update = User.last_update
  end

  def show
    @world = World.includes(:language).where(:short => params[:world]).first
    if @world.nil?
      raise ActiveRecord::RecordNotFound
    end

    @user = User.includes(:achievement_cache,
                          :clan_changes, 
                          :experience_changes,
                          :name_changes,
                          :progresses,
                          :race,
                          :race_changes,
                          :registration, 
                          :world).
                 where(:name => params[:name], :world_id => @world).first
    if @user.nil?
      raise ActiveRecord::RecordNotFound
    end
    
    # achievements
    @achievements_progresses = @user.progresses.includes(:achievement).order("achievements.name asc")
    
    # changes
    @changes = [
      @user.clan_changes.includes(:old_clan, :new_clan).order("users_clan_changes.created_at ASC"),
      @user.name_changes.order("created_at ASC"),
      @user.race_changes.includes(:old_race, :new_race).order("users_race_changes.created_at ASC")
    ]
     
  end
  
  def index     
    # Rasse eingrenzen
    @change_race = true
    
    @model = User
    @attributes = [
      {:db => "#{@model.table_name}.user_id", :human => "user_id"},
      {:db => "#{@model.table_name}.name", :human => "name"},
      {:db => "#{@model.table_name}.experience", :human => "experience"},
      {:human => "race"},
      {:human => "world"},
      {:human => "clan"}     
    ]
    # default
    order = order_from_attributes(@attributes, params[:order], 2)
    
    @users = @model.includes(:world, :clan, :race).where(:world_id => @worlds).
             order("#{order[:db]} #{params[:by]}").references(@model).
             offset(@offset).limit(@limit)   
    
    unless params[:name].nil?
      @users = @users.where("#{@model.table_name}.name LIKE ?", "%#{params[:name]}%") 
      @model = @model.where("#{@model.table_name}.name LIKE ?", "%#{params[:name]}%") 
    end 
    
    unless @race.nil?
      @users = @users.where("#{@model.table_name}.race_id = ?", @race.id)
      @model = @model.where("#{@model.table_name}.race_id = ?", @race.id)
    end
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit) }
      format.html {render 'users/index'}
    end
  end
  
  def new
    @model = UsersNew
    @attributes = [
      {:db => "#{@model.table_name}.user_id", :human => "user_id"},
      {:db => "#{@model.table_name}.name", :human => "name"},
      {:db => "#{@model.table_name}.created_at", :human => "created_at"},
      {:human => "world"}
    ]
    # default
    order = order_from_attributes(@attributes, params[:order], 2)
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]} #{params[:by]}").references(@model).
             offset(@offset).limit(@limit) 
    
    unless params[:name].nil?
      @users = @users.where("#{@model.table_name}.name LIKE ?", "%#{params[:name]}%") 
      @model = @model.where("#{@model.table_name}.name LIKE ?", "%#{params[:name]}%") 
    end    
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit).to_json(:includes => {:world => {:only => [:short]}}) }
      format.jpeg { render_image('jpeg', [
        {:attr => 'user_id', :left => 0},
        {:attr => 'name', :left => 0.1},
        {:attr => 'created_at', :left => 0.3},
        {:attr => 'world_id', :left => 0.9}
      ])}
      format.html {render 'users/index'}
    end
  end
  
  def delete
    @model = UsersDelete
    @attributes = [
      {:human => "user_id", :db => "#{@model.table_name}.user_id"},
      {:human => "name", :db => "#{@model.table_name}.name"},
      {:db => "#{@model.table_name}.created_at", :human => "created_at"},
      {:human => "world", :db => "#{@model.table_name}.world_id"}
    ]
    # default
    order = order_from_attributes(@attributes, params[:order], 2)
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]} #{params[:by]}").
             offset(@offset).limit(@limit)
    
    unless params[:name].nil?
      @users = @users.where("#{@model.table_name}.name LIKE ?", "%#{params[:name]}%") 
      @model = @model.where("#{@model.table_name}.name LIKE ?", "%#{params[:name]}%") 
    end    
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit) }
      format.jpeg { render_image('jpeg', [
        {:attr => 'user_id', :left => 0},
        {:attr => 'name', :left => 0.1},
        {:attr => 'created_at', :left => 0.3},
        {:attr => 'world_id', :left => 0.9}
      ])}
      format.html {render 'users/index'}
    end      
  end
  
  def namechange
    @model = UsersNameChange
    @attributes = [
      {:human => "user_id"},
      {:human => "name_old", :db => "#{@model.table_name}.name_old"},
      {:human => "name_new", :db => "#{@model.table_name}.name_new"},
      {:db => "#{@model.table_name}.created_at", :human => "created_at"},
      {:human => "world", :db => "#{@model.table_name}.world_id"}
    ]
    # default
    order = order_from_attributes(@attributes, params[:order], 2)
    
    @suggest_limit = 0 # disable autocomplete
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]} #{params[:by]}").references(@model).
             offset(@offset).limit(@limit) 
    
    unless params[:name].nil?
      @users = @users.where("#{@model.table_name}.name_old LIKE ? OR #{@model.table_name}.name_new LIKE ?", "%#{params[:name]}%", "%#{params[:name]}%") 
      @model = @model.where("#{@model.table_name}.name_old LIKE ? OR #{@model.table_name}.name_new LIKE ?", "%#{params[:name]}%", "%#{params[:name]}%") 
    end    
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit) }
      format.jpeg { render_image('jpeg', [
        {:attr => 'user_id', :left => 0},
        {:attr => 'name', :left => 0.1},
        {:attr => 'created_at', :left => 0.3},
        {:attr => 'world_name', :left => 0.9}
      ])}
      format.html {render 'users/index'}
    end   
  end
  
  def racechange
    # keine Suche möglich
    @skipsearch = true
    # Rasse eingrenzen
    @change_race = true
    
    @model = UsersRaceChange
    @attributes = [
      {:human => "user_id"},
      {:db => "#{@model.table_name}.race_id_old", :human => "old_race"},
      {:db => "#{@model.table_name}.race_id_new", :human => "new_race"},
      {:db => "#{@model.table_name}.created_at", :human => "created_at"},
      {:human => "world"}
    ]
    # default
    order = order_from_attributes(@attributes, params[:order], 3)
    
    unless @race.nil?
      if order[:human] == 'old_race'
        order[:db] = "#{@model.table_name}.race_id_old = #{@race.id}"
      elsif order[:human] == 'new_race'
        order[:db] = "#{@model.table_name}.race_id_new = #{@race.id}"
      end
    end
    
    order[:db] += " #{params[:by]}"
    
    unless order[:human].eql?('created_at')
      order[:db] += ", #{@model.table_name}.created_at desc"
    end
    
    params[:order] = order[:human]
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]}").references(@model).
             offset(@offset).limit(@limit)
           
    unless @race.nil?
      @model = @model.where("#{@model.table_name}.race_id_old = ? OR "+
                            "#{@model.table_name}.race_id_new = ?", @race.id, @race.id)
      @users = @users.where("#{@model.table_name}.race_id_old = ? OR "+
                            "#{@model.table_name}.race_id_new = ?", @race.id, @race.id)
    end
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit) }
      format.jpeg { render_image('jpeg', [
        {:attr => 'user_id', :left => 0},
        {:attr => 'name', :left => 0.1},
        {:attr => 'created_at', :left => 0.3},
        {:attr => 'world_id', :left => 0.9}
      ])}
      format.html {render 'users/index'}
    end
  end
  
  def clanchange
    @skipsearch = true
    @model = UsersClanChange
    @attributes = [
      {:human => "user_id"},
      {:human => "old_clan"},
      {:human => "new_clan"},
      {:db => "#{@model.table_name}.created_at", :human => "created_at"},
      {:human => "world"}
    ]
    # default
    order = order_from_attributes(@attributes, params[:order], 3)
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]} #{params[:by]}").references(@model).
             offset(@offset).limit(@limit) 
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit) }
      format.jpeg { render_image('jpeg', [
        {:attr => 'user_id', :left => 0},
        {:attr => 'name', :left => 0.1},
        {:attr => 'created_at', :left => 0.3},
        {:attr => 'world_id', :left => 0.9}
      ])}
      format.html {render 'users/index'}
    end
  end
    
  private
  
  def render_image(format, cols)
    if ['jpeg', 'png'].find(format).nil?
      return
    end
    
    require 'rvg/rvg'
    
    font_family = 'Times New Roman'
    h1 = {:font_family => font_family, :font_size => 20}
    th = {:font_family => font_family, :font_size => 15}
    td = {:font_family => font_family, :font_size => 10} 
    
    # messurements
    padding = 10
    line_height = 20
    height  = @users.length * (td[:font_size] + 5) + h1[:font_size] + th[:font_size] * 3
    width   = 430
         
    rvg = Magick::RVG.new(width + padding * 2, height + padding * 2) do |canvas|
      canvas.background_fill = '#51396b'
                
      canvas.text(padding, h1[:font_size], t("#{params[:controller]}.#{params[:action]}.message")).styles(h1)
      canvas.text(padding, h1[:font_size] + th[:font_size], @worlds.collect(&:short).to_sentence).styles(td)
      
      cols.each do |thead|
        canvas.text(padding + (width * thead[:left]).floor, h1[:font_size] + th[:font_size] * 3, 
                    @model.human_attribute_name(thead[:attr])).styles(th)
      end
      
      @users.each_with_index do |user, i|
        cols.each do |tbody|
          if tbody[:attr].eql?('created_at')
            text = l user[tbody[:attr]], :format => :long
          else
            text = user[tbody[:attr]]
          end
          canvas.text(padding + (width * tbody[:left]).floor, line_height * 4 + line_height * i, text).styles(td)
        end
      end
    end
    
    # "render" image
    img = rvg.draw
    img.format = format
    
    render :content_type => "image/#{format}", :text => img.to_blob
  end
end
