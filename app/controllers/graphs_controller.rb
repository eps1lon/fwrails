class GraphsController < ApplicationController
  before_filter :except => :index do
    # parse base64
    unless params[:base64].blank?
      params.merge!(Rack::Utils.parse_nested_query(Base64.decode64(params[:base64])))
    end
    
    @distances_for_select = [
      1.day,
      1.week,
      1.month,
      6.month,
      1.year    
    ].map { |d| [view_context.distance_of_time_in_words(d), d]}
    @distances_for_select.insert(0, [view_context.t("graphs.config.distance_infty"), 0])
    
    # constraintrs
    @fonts = %w{Arial Verdana}
    
    # std
    @config = {
      :checkboxes   => Hash[*%w{hide_dots true hide_lines false}],
      :colors     => Hash[*%w{bg white line black}],
      :font       => @fonts[0],
      :font_sizes => Hash[*%w{title 14 legend_graph 12 label 10}],
      :distance   => 0
    }
    
    # disable config
    @config[:colors] = {}
    @config[:font] = nil
    @config[:font_sizes] = {}
    @config[:checkboxes] = {}
    
    # merge params into config
    @config[:distance] ||= params[:distance]
  end
  
  def index
    
  end
  
  def achievements
    # data for the graph
    @data = {:mode => params[:mode]}
  
    # worlds with achievements    
    @achievement_worlds = Achievement.worlds.order("id asc")
    
    # achievements
    @achievements = Achievement.base_stage.order(:name)
    
    # searched achievement
    @achievement_identifier = :achievement_id
    achievement = @achievements.where(@achievement_identifier => params[:achievement])
                               .first
    
    unless achievement.nil?
      @data[:achievement] = achievement[@achievement_identifier]
    end
    
    # graph ready to be displayed
    @graph_ready = !@data[:achievement].nil?

    if params[:mode] == 'world'
      # for the select tag
      @world_value = :short
      
      # 
      @worlds_for_select = @achievement_worlds.map { |w| [w.name, w[@world_value]] }
      @worlds_for_select.insert(0, [view_context.t("graphs.achievements.data.world.worlds_all"), 0])
      
      # searched worlds
      @data[:worlds] = @achievement_worlds.where(@world_value => params[:worlds])
                                          .collect(&@world_value)
      
      # all worlds if none selected
      @data[:worlds] << 0 if @data[:worlds].empty?
      
      @graph_ready &&= !@data[:worlds].empty?
    elsif params[:mode] == 'user'
      std_user_param = {:world => {}}
      params[:users] ||= []
      params[:user] ||= std_user_param
      
      user = User.includes(:world).find_by_name_and_world_short(params[:user][:name], params[:user][:world][:short])
      unless user.nil?
        params[:users] << user.to_param
        params[:user] = std_user_param
      end
      
      @users_for_select = User.from_params(params[:users]).uniq.map do |user|
        [user.name_primary, user.to_param]
      end
      @data[:users] = @users_for_select.collect{ |u| u[1] }
        
      @graph_ready &&= !@data[:users].empty?
    end
    
    respond_to do |format|
      format.json do 
        render json: {
          :users => @users_for_select,
          :user => params[:user]
        }
      end
      format.html 
    end
  end
  
  def statistics
    # graph ready
    @graph_ready = true
    
    # data for graph
    @data = {}
    
    # for the select tag
    @world_value = :short

    # Welten
    @worlds = World.all
    @worlds_for_select = @worlds.map { |w| [w.name, w[@world_value]] }
    @worlds_for_select.insert(0, [view_context.t("graphs.achievements.data.world.worlds_all"), 0])

    # searched worlds
    @data[:worlds] = @worlds.where(@world_value => params[:worlds])
                                        .collect(&@world_value)

    # all worlds if none selected
    @data[:worlds] << 0 if @data[:worlds].empty?
    
    # statistics
    @statistics = Statistic.all
    @statistics_for_select = @statistics.map { |s| [I18n.t("statistics.names.#{s.name}"), s.id] }
    @data[:statistic] = params[:statistic] if @statistics.collect(&:id).include?(params[:statistic].to_i)
    
    # graph ready
    @graph_ready &&= !@data[:statistic].nil?
  end
end
