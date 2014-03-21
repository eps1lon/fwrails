class StatisticsController < ApplicationController
  before_filter [:index, :show] do
    @worlds = @worlds_all = World.includes(:language)
    if params[:world]
      @worlds = @worlds.where(:short => params[:world]) 
      raise ActiveRecord::RecordNotFound if @worlds.first.nil?
    end
    
  end
  
  def index
    @statistics = Statistic.with_achievements(:in_worlds => @worlds)
  end
  
  def show
    statistic = Statistic.where(:name => params[:name]).first
                  
    if statistic.nil? # achievement_statistic
      @worlds = []
      last_update = UsersAchievementsCache.last_update
      
      @debug = Statistic.achievement_statistic(Statistic.available_stats[params[:name]], :group_by => :world_id)
      Statistic.achievement_statistic(Statistic.available_stats[params[:name]], :group_by => :world_id).each do |world_stat|
        @worlds << StatisticChange.new(:value => world_stat[1], 
                                       :world_id => world_stat[0][1],
                                       :created_at => last_update)
      end
    else
      @worlds = statistic.world_grouped
    end
  end
end
