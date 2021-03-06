class AchievementsController < ApplicationController
  before_filter :only => [:index, :unachieved, :rank] do 
    @last_update = UsersAchievementsCache.last_update

    @all_worlds = @worlds = World.includes(:language).where(:language_id => 1)
    unless params[:world].nil?
      if params[:world][0].eql?('!')
        @worlds = @all_worlds.where.not(:short => params[:world][1..-1])
      else
        @worlds = @all_worlds.where(:short => params[:world])
      end
    end
  end
  
  def index
    @achievements = Achievement.base_stage.including_achiev_count(:in_worlds => @worlds)
                               .order("name asc")
  end
  
  def rank 
    @params = rank_params
    
    @limit = 20
    @offset = (@params[:page] - 1) * @limit
    
    # define sorting order for sort_links
    if @params[:by].to_s.downcase.eql?('desc') 
      @by = 'asc'
    else
      @by = 'desc'
    end
    
    @users = UsersAchievementsCache.includes(:user, :achievements, :world).
                                    where(:deleted => false, :world_id => @worlds.collect { |world| world.id })
    
    if UsersAchievementsCache.column_names.include?(@params[:order].to_s)
      @order = [@params[:order]]
    else
      @order = ['count']
    end
    
    @order << 'reward_collected'
    
    # groups of achievements which can be display
    @achiev_groups = Achievement.base_stage.order(:name)
    
    # show correspondending achievement_progress
    if @params[:ids]
      @achievements = @achiev_groups.where(:achievement_id => params[:ids].split(','))
      @achiev_groups = @achiev_groups.where("achievement_id NOT IN (?)", @achievements.collect { |a| a.group }.join(','))
      
      if @params[:order].is_numeric? && !@achievements.to_a.select {|a| a.achievement_id == @params[:order].to_i}.empty?
        @users = @users.joins(:achievements).
                        where(:users_achievements => {:achievement_id => @params[:order]})
        @order = ["#{UsersAchievements.table_name}.stage", 
                  "#{UsersAchievements.table_name}.progress"]
      end
    else
      @achievements = []
    end
    
    @scope = @users
    @users = @users.order(@order.map {|order| "#{order} #{@params[:by]}"}.join(','))
    @users = @users.offset(@offset).limit(20)
    
    if stale?(etag: [@achievements, @users], last_modified: @users.take.try(:updated_at), public: true)
      # users_achievements
      @users_achievements = {}
      @users.each do |user|
        @users_achievements[user.to_param] = user.achievements.where(:achievement_id => @achievements.collect(&:achievement_id))
      end
      
      respond_to do |format|
        format.html
      end
    end
  end
  
  def group_progress
    # format of params[:users]: user[,user]...
    
    users = User.from_params(params[:users].split(','))
                .includes(:progresses)
                .where(users_achievements: {:achievement_id => params[:group]})
    achievements = users.map { |u| u.progresses[0] unless u.progresses.empty? }
    
    respond_to do |format|
      format.json { render json: achievements, only: [:user_id, :world_id, :stage, :progress] }
    end
  end
  
  def show
    @worlds = World.includes(:language).where(:language_id => 1)
    
    @stages = Achievement.where(:name => params[:name]).order('stage asc')
    
    if @stages.empty?
      raise ActiveRecord::RecordNotFound.new(params[:name])
    else
      if params[:stage].is_numeric?
        @achievement = @stages.where(:stage => params[:stage]).first
      else
        @achievement = @stages[0]
      end
      
      raise ActiveRecord::RecordNotFound.new(@stages[0].name(params[:stage])) if @achievement.nil?
    end
    
    # for cache_key in fragment caching
    @last_update = UsersAchievementsCache.last_update
    
    @title = params[:name]
    
    if stale?(etag: @achievement, last_modified: @last_update, public: true)
      # various stats about this achievement each world
      # achieved 
      @achieved = @achievement.achieved.group(:world_id)

      @achiev_counts = []
      @achieved.count.each do |count|
        @achiev_counts << {
          :count => count[1],
          :world => @worlds[count[0]-1]
        }
      end

      @progress_sums = []
      @achieved.sum(:progress).each do |progress_sum|
        @progress_sums << {
          :sum => progress_sum[1],
          :world => @worlds[progress_sum[0]-1]
        }
      end

      @furthest = @achievement.furthest.includes(:world, :user).group(:world_id)
      @worlds.each do |world|
        #@furthest << 
      end
      #@furthest.compact!
    end
  end

  def unachieved
    if stale?(etag: @worlds, last_modified: [Achievement.maximum(:created_at), UsersAchievementsCache.last_update].max, public: true)
      @closest = {}
      @achievements = Achievement.unachieved(:in_worlds => @worlds).
                                  order("achievements.name")

      @achievements.each do |achievement|
        @closest[achievement.id] = achievement.closest.includes(:user, :world).
                                               where(:world_id => @worlds.collect(&:id)).
                                               limit(1).first
      end
      
      respond_to do |format|
        format.html
      end
    end
  end
  
  private
  def rank_params
    # defaults and sanitize
    params[:order] ||= 'count'
    params[:page] = [params[:page].to_i, 1].max
    # strong params
    filter_sql_by(params.permit(:ids, :page, :order, :by, :world), :by, :desc)
  end
end