class Statistic < ActiveRecord::Base
  attr_accessor :last_change
  has_many :changes, :class_name => 'StatisticChange' do
    def last
      order("created_at desc").limit(1).take
    end
  end
  
  def last_change
    @last_change || changes.last
  end
  
  def world_grouped
    StatisticChange.select("b.*").from("(#{StatisticChange.where(:statistic_id => self).order("created_at desc").to_sql}) as b").group(:world_id)
  end
  
  def self.with_achievements(options = {})
    options[:in_worlds] ||= World.all
    achievements_last_update = UsersAchievementsCache.last_update
    
    achievement_stats = self.available_stats
    stats = []
    
    self.achievement_statistic(achievement_stats.values, options).each do |stat|
      stats << self.achievement_statistic_to_statistic(stat, 
                                                      achievements_last_update)
    end
    
    # self.includes(:last_change) much slower than calling this relation within each statistic
    self.all.each do |stat|
      stat.last_change = stat.changes.where(world_id: options[:in_worlds]).last
    end
  end
  
  def self.achievement_statistic(group, options = {})
    options[:in_worlds] ||= World.all
    group_by = [:achievement_id]
    group_by << options[:group_by] if options[:group_by]
    
    UsersAchievements.where(:achievement_id => group, :world_id => options[:in_worlds]).
                      group(group_by).sum(:progress)
  end
  
  def self.achievement_statistic_to_statistic(progress, last_update = nil)
    last_update ||= UsersAchievementsCache.last_update
    
    Statistic.new(:name => Statistic.available_stats.index(progress[0]),
             :last_change => StatisticChange.new(:value => progress[1], :created_at => last_update))
  end
  
  def self.available_stats
    {
      'bloodsamples_collected'             => 21,
      'made_chaoslab_items'                => 22,
      'extinguished'                       => 20,
      'donations_got'                      => 17,
      'surveys'                            => 28,
      'tissuesamples_collected'            => 2, 
      'group_inspirations'                 => 7,
      'switched_controller_tower_alliance' => 24,
      'completed_missions'                 => 6,
      'chased_npc'                         => 15,
      'collected_plants'                   => 16,
      'killed_phasenpc'                    => 13,
      'completed_treassure_maps'           => 25,
      'exploded_chaoslabs'                 => 23,
      'active_kills'                       => 18,
      'collected_grotto_moos'              => 11,
      'killed_undarons'                    => 5,
      'killed_underworld_demons'           => 26,
      'became_traitor'                     => 30,
      'aggressive_npc'                     => 31,
      'ingerium'                           => 32,
      'goat_herd'                          => 33,
      'goat_kill'                          => 34,
      'cooks'                              => 35,
      'group_kills'                        => 36,
      'paid_dividend'                      => 37,
      'opened_locks'                       => 38,
      'worm_segments'                      => 39,
      'casino_won'                         => 40,
      'casino_lost'                        => 41,
      'plants_small'                       => 42
    }
  end
  
  def self.last_update
    StatisticChange.order("created_at desc").take.created_at
  end
end
