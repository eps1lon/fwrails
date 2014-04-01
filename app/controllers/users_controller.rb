class UsersController < ApplicationController 
  before_filter :only => [:index, :new, :delete, :racechange, :namechange, :clanchange] do 
    @std_params = list_params
    
    @limit = 20
    @suggest_limit = 5
    @offset = (@std_params[:page]  - 1) * @limit
    
    if @std_params[:by].to_s.downcase.eql?('desc') # define sorting order for sort_links
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
    user_params = list_params
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
    order = order_from_attributes(@attributes, user_params[:order], 2)
    
    @users = @model.includes(:world, :clan, :race).where(:world_id => @worlds).
             order("#{order[:db]} #{user_params[:by]}").references(@model).
             offset(@offset).limit(@limit)   
    
    unless params[:name].nil?
      @users = @users.where("#{@model.table_name}.name LIKE ?", "%#{user_params[:name]}%") 
      @model = @model.where("#{@model.table_name}.name LIKE ?", "%#{user_params[:name]}%") 
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
    user_params = list_params
    @model = UsersNew
    @attributes = [
      {:db => "#{@model.table_name}.user_id", :human => "user_id"},
      {:db => "#{@model.table_name}.name", :human => "name"},
      {:db => "#{@model.table_name}.created_at", :human => "created_at"},
      {:human => "world"}
    ]
    # default
    order = order_from_attributes(@attributes, user_params[:order], 2)
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]} #{params[:by]}").references(@model).
             offset(@offset).limit(@limit) 
    
    unless params[:name].nil?
      @users = @users.where("#{@model.table_name}.name LIKE ?", "%#{user_params[:name]}%") 
      @model = @model.where("#{@model.table_name}.name LIKE ?", "%#{user_params[:name]}%") 
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
    user_params = list_params
    @model = UsersDelete
    @attributes = [
      {:human => "user_id", :db => "#{@model.table_name}.user_id"},
      {:human => "name", :db => "#{@model.table_name}.name"},
      {:db => "#{@model.table_name}.created_at", :human => "created_at"},
      {:human => "world", :db => "#{@model.table_name}.world_id"}
    ]
    # default
    order = order_from_attributes(@attributes, user_params[:order], 2)
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]} #{user_params[:by]}").
             offset(@offset).limit(@limit)
    
    unless params[:name].nil?
      @users = @users.where("#{@model.table_name}.name LIKE ?", "%#{user_params[:name]}%") 
      @model = @model.where("#{@model.table_name}.name LIKE ?", "%#{user_params[:name]}%") 
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
    user_params = list_params
    @model = UsersNameChange
    @attributes = [
      {:human => "user_id"},
      {:human => "name_old", :db => "#{@model.table_name}.name_old"},
      {:human => "name_new", :db => "#{@model.table_name}.name_new"},
      {:db => "#{@model.table_name}.created_at", :human => "created_at"},
      {:human => "world", :db => "#{@model.table_name}.world_id"}
    ]
    # default
    order = order_from_attributes(@attributes, user_params[:order], 2)
    
    @suggest_limit = 0 # disable autocomplete
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]} #{user_params[:by]}").references(@model).
             offset(@offset).limit(@limit) 
    
    unless params[:name].nil?
      @users = @users.where("#{@model.table_name}.name_old LIKE ? OR #{@model.table_name}.name_new LIKE ?", "%#{user_params[:name]}%", "%#{params[:name]}%") 
      @model = @model.where("#{@model.table_name}.name_old LIKE ? OR #{@model.table_name}.name_new LIKE ?", "%#{user_params[:name]}%", "%#{params[:name]}%") 
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
    user_params = list_params
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
    order = order_from_attributes(@attributes, user_params[:order], 3)
    
    unless @race.nil?
      if order[:human] == 'old_race'
        order[:db] = "#{@model.table_name}.race_id_old = #{@race.id}"
      elsif order[:human] == 'new_race'
        order[:db] = "#{@model.table_name}.race_id_new = #{@race.id}"
      end
    end
    
    order[:db] += " #{user_params[:by]}"
    
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
    user_params = list_params
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
    order = order_from_attributes(@attributes, user_params[:order], 3)
    
    @users = @model.where(:world_id => @worlds).
             order("#{order[:db]} #{user_params[:by]}").references(@model).
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
  
  def list_params
    params[:page] = params[:page].to_i
    filter_sql_by(params.permit(:action, :world, :order, :by, :name, :page, :race), :by, :desc)
  end
end
