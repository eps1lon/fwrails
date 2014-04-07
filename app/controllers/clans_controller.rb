class ClansController < ApplicationController
  before_filter :only => [:index, :new, :delete, :leader_change, :coleader_change, :tag_change, :name_change] do 
    @params = list_params
       
    @limit = 20
    @suggest_limit = 5
    @offset = ([1, @params[:page].to_i].max - 1) * @limit
    
    if @params[:by].to_s.downcase.eql?('desc') # define sorting order for sort_links
      @by = 'asc'
    else
      @by = 'desc'
    end
    
    @worlds = @worlds_all = World.includes(:language).order("id asc")
    
    unless params[:world].nil?
      @worlds = World.where(:short => params[:world]).references(:world)
    end
    
    @last_update = Clan.last_update
  end
  
  before_filter :common_new_delete,    :only => [:delete, :new]
  before_filter :common_leader_change, :only => [:leader_change, :coleader_change] 
  before_filter :common_text_change,   :only => [:name_change, :tag_change]

  def index
    clan_params = @params
    @model = Clan.where(:world_id => @worlds)
    
    # create attributes
    @attributes = [
      {human: "clan_id", db: "#{@model.table_name}.clan_id"},
      {human: "tag", db: "#{@model.table_name}.tag"},
      {human: "name", db: "#{@model.table_name}.name"},
      {human: "sum_experience", db: "#{@model.table_name}.sum_experience"},
      {human: "member_count", db: "#{@model.table_name}.member_count"},
      {human: "leader_id"},
      {human: "coleader_id"},
      {human: "world_id"},
    ]    
    # default
    order = order_from_attributes(@attributes, clan_params[:order], 3)
    
    @clans = @model.preload(:coleader, :leader, :world).
                    order("#{order[:db]} #{clan_params[:by]}").
                    offset(@offset).limit(@limit) 
    
    unless clan_params[:name].nil?
      @clans = @clans.where("#{@model.table_name}.name LIKE ?", "%#{clan_params[:name]}%").references(:clan)
      @model = @model.where("#{@model.table_name}.name LIKE ?", "%#{clan_params[:name]}%").references(:clan)
    end
    
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
  end
  
  def tag_change    
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
    clan_params = @params
    @model = Object.const_get("Clans#{action_name.camelize}").where(:world_id => @worlds)
    
    # create attributes array
    @attributes = [
      {:human => "clan_id", :db => "#{@model.table_name}.clan_id"},
      {:human => "tag", :db => "#{@model.table_name}.tag"},
      {:human => "created_at", :db => "#{@model.table_name}.created_at"},
      {:human => "world_id"}
    ]
    
    # default
    order = order_from_attributes(@attributes, clan_params[:order], 2)
    
    # query
    @clans = @model.preload(:clan, :world).order("#{order[:db]} #{clan_params[:by]}").
                    offset(@offset).limit(@limit) 
    
    unless params[:name].nil?
      @clans = @clans.where("#{@model.table_name}.tag LIKE ?", "%#{clan_params[:name]}%") 
      @model = @model.where("#{@model.table_name}.tag LIKE ?", "%#{clan_params[:name]}%") 
    end
    
    respond_to do |format|
      format.json { render :json => @clans.limit(@suggest_limit), methods: :name_primary }
      format.html { render 'clans/index'}
    end
  end
  
  def common_leader_change
    clan_params = @params
    type = action_name.split("_").slice(0).to_s
    @model = Object.const_get("Clans#{type.capitalize}Change").where(:world_id => @worlds)
    
    # create attribute array
    @attributes = [
      {:human => "clan_id", :db => "#{@model.table_name}.clan_id"},
      {:human => "#{type}_id_old"},
      {:human => "#{type}_id_new"},
      {:human => "created_at", :db => "#{@model.table_name}.created_at"},
      {:human => "world_id", :db => "#{@model.table_name}.world_id"}
    ]
    
    # default
    order = order_from_attributes(@attributes, clan_params[:order], 3)
    
    # query
    # since we are using name_primary in the view rails also queries clan.leader_old.world
    # this is redundant but we know no way to prevent this kind of behavior
    @clans = @model.preload(:clan, :world, "#{type}_old".to_sym, "#{type}_new".to_sym).
                    order("#{order[:db]} #{clan_params[:by]}").offset(@offset).limit(@limit) 
    
    @skipsearch = true
    respond_to do |format|
      format.html { render 'clans/index'}
    end
  end
  
  def common_text_change
    clan_params = @params
    type = action_name.split("_").slice(0).to_s
    @model = Object.const_get("Clans#{type.capitalize}Change").where(:world_id => @worlds)
    
    # create attr array
    @attributes = [
      {:human => "clan_id"},
      {:human => "#{type}_old"},
      {:human => "#{type}_new"},
      {:human => "created_at", :db => "#{@model.table_name}.created_at"},
      {:human => "world_id"}
    ]
    
    # default
    order = order_from_attributes(@attributes, clan_params[:order], 3)
    
    # query
    @clans = @model.preload(:clan, :world).
                    order("#{order[:db]} #{clan_params[:by]}").offset(@offset).limit(@limit) 
    
    # render
    @skipsearch = true
    respond_to do |format|
      format.html { render 'clans/index'}
    end
  end
  
  def list_params
    params[:page] = [1, params[:page].to_i].max
    filter_sql_by(params.permit(:action, :world, :order, :by, :tag, :page, :name), :by, :desc)
  end
end
