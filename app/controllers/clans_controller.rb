class ClansController < ApplicationController
  before_filter :only => [:index, :new, :delete, :leader_change, :coleader_change, :tag_change, :name_change] do 
    # get model from action
    @scope = Object.const_get("clans_#{action_name.gsub("index", "")}".camelize.singularize)
    
    # get hole recording period
    @hole_recording_period = @scope.record_period
    
    @params = list_params
    
    # submitted period
    @recording_period = (@params[:starttime].try(:to_date) || @hole_recording_period.begin)..(@params[:endtime].try(:to_date) || @hole_recording_period.end)
    
    # we have a period and not only one timeframe
    @has_recording_period = @hole_recording_period.begin < @hole_recording_period.end
    
    @limit = 20
    @suggest_limit = 5
    @offset = (@params[:page] - 1) * @limit
    
    if @params[:by].to_s.downcase.eql?('desc') # define sorting order for sort_links
      @by = 'asc'
    else
      @by = 'desc'
    end
    
    @worlds = @worlds_all = World.includes(:language).order("id asc")
    
    unless params[:world].nil?
      @worlds = World.where(:short => params[:world]).references(:world)
    end
    
    @last_update = @hole_recording_period.end
  end
  
  before_filter :common_new_delete,    :only => [:delete, :new]
  before_filter :common_leader_change, :only => [:leader_change, :coleader_change] 
  before_filter :common_text_change,   :only => [:name_change, :tag_change]

  def index
    @scope = Clan.where(:world_id => @worlds).name_like(@params[:name])
    
    # create attributes
    @attributes = [
      {human: "clan_id", db: "#{@scope.table_name}.clan_id"},
      {human: "tag", db: "#{@scope.table_name}.tag"},
      {human: "name", db: "#{@scope.table_name}.name"},
      {human: "sum_experience", db: "#{@scope.table_name}.sum_experience"},
      {human: "member_count", db: "#{@scope.table_name}.member_count"},
      {human: "leader_id"},
      {human: "coleader_id"},
      {human: "world_id"},
    ]    
    # default
    order = order_from_attributes(@attributes, @params[:order], 3)
    
    @clans = @scope.preload(:coleader, :leader, :world).
                    order("#{order[:db]} #{@params[:by]}").
                    offset(@offset).limit(@limit) 

    respond_to do |format|
      format.json { render :json => @clans.limit(@suggest_limit), methods: :name_primary }
      format.html { render 'clans/index'}
    end
  end
  
  #new_delete
  def delete
  end
  
  def new
  end
  
  #leaderchange
  def coleader_change
  end
  
  def leader_change
  end
  
  # textchange
  def name_change    
    @scope = @scope.where(:world_id => @worlds).name_like(@params[:name]).in_recording_period_date(@recording_period)
  end
  
  def tag_change    
    @scope = @scope.where(:world_id => @worlds).tag_like(@params[:name]).in_recording_period_date(@recording_period)
  end
  
  def show
    @world = World.where(:short => params[:world]).first
    
    raise ActiveRecord::RecordNotFound if @world.nil?
    
    @clan = Clan.where(:world_id => @world.id, :clan_id => params[:id]).
                 includes(:adds, :coleader, :leader, :members, :outs).first
               
    raise ActiveRecord::RecordNotFound if @clan.nil?

    @members = @clan.members.order("name asc")
    
    @xp_min_member = @clan.members.order("experience asc").first
    @xp_max_member = @clan.members.order("experience desc").first
    
    # undefined method `order' for nil:NilClass
    # dont want to check for nil? every time so we put the order in the view
    #@changes = {
    #  :adds     => @clan.changes[:adds].order("created_at desc"),
    #  :coleader => @clan.changes[:coleade].order("created_at desc"),
    #  :leader   => @clan.changes[:leader].order("created_at desc"),
    #  :name     => @clan.changes[:name].order("created_at desc"),
    #  :outs     => @clan.changes[:outs].order("created_at desc"),
    #  :tag      => @clan.changes[:tag].order("created_at desc")
    #}
    
  end
  
  private
  
  def common_new_delete
    @scope = @scope.where(:world_id => @worlds).tag_like(@params[:name]).in_recording_period_date(@recording_period)
    
    # create attributes array
    @attributes = [
      {:human => "clan_id", :db => "#{@scope.table_name}.clan_id"},
      {:human => "tag", :db => "#{@scope.table_name}.tag"},
      {:human => "created_at", :db => "#{@scope.table_name}.created_at"},
      {:human => "world_id"}
    ]
    
    # default
    order = order_from_attributes(@attributes, @params[:order], 2)
    
    # query
    @clans = @scope.preload(:clan, :world).order("#{order[:db]} #{@params[:by]}").
                    offset(@offset).limit(@limit) 
    
    respond_to do |format|
      format.json { render :json => @clans.limit(@suggest_limit), methods: :name_primary }
      format.html { render 'clans/index'}
    end
  end
  
  def common_leader_change
    type = action_name.split("_").slice(0).to_s
    @scope = @scope.where(:world_id => @worlds).in_recording_period_date(@recording_period)
    
    # create attribute array
    @attributes = [
      {:human => "clan_id", :db => "#{@scope.table_name}.clan_id"},
      {:human => "#{type}_id_old"},
      {:human => "#{type}_id_new"},
      {:human => "created_at", :db => "#{@scope.table_name}.created_at"},
      {:human => "world_id", :db => "#{@scope.table_name}.world_id"}
    ]
    
    # default
    order = order_from_attributes(@attributes, @params[:order], 3)
    
    # query
    # since we are using name_primary in the view rails also queries clan.leader_old.world
    # this is redundant but we know no way to prevent this kind of behavior
    @clans = @scope.preload(:clan, :world, "#{type}_old".to_sym, "#{type}_new".to_sym).
                    order("#{order[:db]} #{@params[:by]}").offset(@offset).limit(@limit) 
    
    @skipsearch = true
    respond_to do |format|
      format.html { render 'clans/index'}
    end
  end
  
  def common_text_change
    type = action_name.split("_").slice(0).to_s
    
    # create attr array
    @attributes = [
      {:human => "clan_id"},
      {:human => "#{type}_old"},
      {:human => "#{type}_new"},
      {:human => "created_at", :db => "#{@scope.table_name}.created_at"},
      {:human => "world_id"}
    ]
    
    # default
    order = order_from_attributes(@attributes, @params[:order], 3)
    
    # query
    @clans = @scope.preload(:clan, :world).
                    order("#{order[:db]} #{@params[:by]}").offset(@offset).limit(@limit) 
    
    # render
    @skipsearch = true
    respond_to do |format|
      format.html { render 'clans/index'}
    end
  end
  
  def list_params
    @errors ||= {}
    # validating
    params[:page] = [1, params[:page].to_i].max
    [:starttime, :endtime].each do |datetime|
      params[datetime] = params[datetime].try(:to_date)
      
      if !params[datetime].nil? && !@hole_recording_period.cover?(params[datetime])
        (@errors[datetime] ||= []) << :not_in_range
      end
    end
    filter_sql_by(params.permit(:action, :world, :order, :by, :tag, :page, :name, :starttime, :endtime), :by, :desc)
  end
end
