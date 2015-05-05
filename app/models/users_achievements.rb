class UsersAchievements < ActiveRecord::Base
  include DeleteMarkable
  include UserNaming
  include UserUrls
  
  self.primary_keys = :user_id, :world_id, :achievement_id
  
  belongs_to :achievement, :primary_key => [:achievement_id, :stage],
                           :foreign_key => [:achievement_id, :stage] 
  has_many :stages, :class_name => 'Achievement',
                    :foreign_key => :achievement_id,
                    :primary_key => :achievement_id
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :world
  has_many :diffs, :class_name => 'UsersAchievementsChange',
                   :primary_key => :achievement_id
  
  on_deleted_nullify_relation :user
  
  scope :active, -> { where(deleted: false) }
  
  def maxed?
    next_stage.nil?
  end
  
  def needed
    next_stage.try(:needed) || achievement.try(:needed)
  end
  
  def next_stage
    self.stages.to_a.select { |achievement| achievement.stage == self.stage + 1}[0]
  end
end
