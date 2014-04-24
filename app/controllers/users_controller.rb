class UsersController < ApplicationController
  # init error hash
  before_filter do
    @errors = {}
  end
  
  before_filter :only => [:index, :new, :delete, :race_change, :name_change, :clan_change] do 
    # get model from action
    @scope = Object.const_get("users_#{action_name.gsub("index", "")}".camelize.singularize)
    
    # get hole recording period
    @hole_recording_period = @scope.record_period
    
    @params = list_params
    
    # submitted period
    @recording_period = (@params[:starttime].try(:to_date) || @hole_recording_period.begin)..(@params[:endtime].try(:to_date) || @hole_recording_period.end)
    
    # we have a period and not only one timeframe
    @has_recording_period = @hole_recording_period.begin < @hole_recording_period.end

    @limit = 20
    @suggest_limit = 5
    @offset = (@params[:page]  - 1) * @limit
    
    if @params[:by].to_s.downcase.eql?('desc') # define sorting order for sort_links
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
    
    @last_update = @hole_recording_period.end
  end

  def show
    @world = World.includes(:language).where(:short => params[:world]).first
    if @world.nil?
      raise ActiveRecord::RecordNotFound
    end

    @user = User.includes(:achievement_cache,
                          :experience_changes,
                          :race,
                          :registration, 
                          :world).
                 where(:name => params[:name], :world_id => @world).first
    if @user.nil?
      raise ActiveRecord::RecordNotFound
    end
    
    # achievements
    @achievements_progresses = @user.progresses.order("achievements.name asc").references(:achievements)
    
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
    
    @timeframe = false
    
    @scope = User.all
    @attributes = [
      {:db => "#{@scope.table_name}.user_id", :human => "user_id"},
      {:db => "#{@scope.table_name}.name", :human => "name"},
      {:db => "#{@scope.table_name}.experience", :human => "experience"},
      {:human => "race"},
      {:human => "world"},
      {:human => "clan"}     
    ]
    # default
    order = order_from_attributes(@attributes, user_params[:order], 2)
    
    @users = @scope.preload(:clan, :race, :world).where(:world_id => @worlds).
             order("#{order[:db]} #{user_params[:by]}").
             offset(@offset).limit(@limit)   
    
    unless params[:name].nil?
      @users = @users.where("#{@scope.table_name}.name LIKE ?", "%#{user_params[:name]}%") 
      @scope = @scope.where("#{@scope.table_name}.name LIKE ?", "%#{user_params[:name]}%") 
    end 
    
    unless @race.nil?
      @users = @users.where("#{@scope.table_name}.race_id = ?", @race.id)
      @scope = @scope.where("#{@scope.table_name}.race_id = ?", @race.id)
    end
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit), methods: :name_primary }
      format.html {render 'users/index'}
    end
  end
  
  def new
    user_params = list_params
    @scope = UsersNew.where(world_id: @worlds).name_like(@params[:name]).in_recording_period_date(@recording_period)
    @attributes = [
      {:db => "#{@scope.table_name}.user_id", :human => "user_id"},
      {:db => "#{@scope.table_name}.name", :human => "name"},
      {:db => "#{@scope.table_name}.created_at", :human => "created_at"},
      {:human => "world"}
    ]
    # default
    order = order_from_attributes(@attributes, user_params[:order], 2)
    
    @users = @scope.preload(:user, :world).
             order("#{order[:db]} #{@params[:by]}").
             offset(@offset).limit(@limit) 
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit), methods: :name_primary }
      format.html {render 'users/index'}
    end
  end
  
  def delete
    user_params = @params
    @scope = UsersDelete.where(world_id: @worlds).name_like(@params[:name]).in_recording_period_date(@recording_period)
    @attributes = [
      {:human => "user_id", :db => "#{@scope.table_name}.user_id"},
      {:human => "name", :db => "#{@scope.table_name}.name"},
      {:db => "#{@scope.table_name}.created_at", :human => "created_at"},
      {:human => "world", :db => "#{@scope.table_name}.world_id"}
    ]
    # default
    order = order_from_attributes(@attributes, user_params[:order], 2)
    
    @users = @scope.preload(:world).
             order("#{order[:db]} #{user_params[:by]}").
             offset(@offset).limit(@limit)
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit), methods: :name_primary }
      format.html {render 'users/index'}
    end      
  end
  
  def name_change
    @suggest_limit = 5
    
    @scope = @scope.where(world_id: @worlds).name_like(@params[:name]).in_recording_period_date(@recording_period)
    
    @attributes = [
      {:human => "user_id"},
      {:human => "name_old", :db => "#{@scope.table_name}.name_old"},
      {:human => "name_new", :db => "#{@scope.table_name}.name_new"},
      {:db => "#{@scope.table_name}.created_at", :human => "created_at"},
      {:human => "world", :db => "#{@scope.table_name}.world_id"}
    ]
    # default
    order = order_from_attributes(@attributes, @params[:order], 3)
    
    @users = @scope.preload(:user, :world).
             order("#{order[:db]} #{@params[:by]}").
             offset(@offset).limit(@limit) 

    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit), methods: :name_primary }
      format.html {render 'users/index'}
    end   
  end
  
  def race_change
    user_params = @params
    # keine Suche möglich
    @skipsearch = true
    # Rasse eingrenzen
    @change_race = true
    
    @scope = UsersRaceChange.where(:world_id => @worlds).race(@race).in_recording_period_date(@recording_period)
    @attributes = [
      {:human => "user_id"},
      {:db => "#{@scope.table_name}.race_id_old", :human => "old_race"},
      {:db => "#{@scope.table_name}.race_id_new", :human => "new_race"},
      {:db => "#{@scope.table_name}.created_at", :human => "created_at"},
      {:human => "world"}
    ]
    # default
    order = order_from_attributes(@attributes, user_params[:order], 3)

    order[:db] += " #{user_params[:by]}"
    
    unless order[:human].eql?('created_at')
      order[:db] += ", #{@scope.table_name}.created_at desc"
    end
    
    params[:order] = order[:human]
    
    @users = @scope.preload(:new_race, :old_race, :user, :world).
             order("#{order[:db]}").
             offset(@offset).limit(@limit)
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit), methods: :name_primary }
      format.html {render 'users/index'}
    end
  end
  
  def clan_change
    user_params = @params
    @skipsearch = true
    
    @scope = UsersClanChange.where(:world_id => @worlds).in_recording_period_date(@recording_period)
    
    @attributes = [
      {:human => "user_id"},
      {:human => "old_clan"},
      {:human => "new_clan"},
      {:db => "#{@scope.table_name}.created_at", :human => "created_at"},
      {:human => "world"}
    ]
    # default
    order = order_from_attributes(@attributes, user_params[:order], 3)
    
    @users = @scope.preload(:new_clan, :old_clan, :user, :world).
             order("#{order[:db]} #{user_params[:by]}").
             offset(@offset).limit(@limit) 
    
    respond_to do |format|
      format.json { render :json => @users.limit(@suggest_limit), methods: :name_primary }
      format.html {render 'users/index'}
    end
  end
    
  private
  
  def list_params
    # validating
    params[:page] = [1, params[:page].to_i].max
    [:starttime, :endtime].each do |datetime|
      params[datetime] = params[datetime].try(:to_date)
      
      if !params[datetime].nil? && !@hole_recording_period.cover?(params[datetime])
        (@errors[datetime] ||= []) << :not_in_range
      end
    end
    filter_sql_by(params.permit(:action, :world, :order, :by, :name, :page, :race, :starttime, :endtime), :by, :desc)
  end
end
