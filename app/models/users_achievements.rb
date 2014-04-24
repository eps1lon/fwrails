class UsersAchievements < ActiveRecord::Base
  include DeleteMarkable
  include UserNaming
  include UserUrls
  
  self.primary_keys = :user_id, :world_id, :achievement_id
  
  belongs_to :achievement, :primary_key => [:achievement_id, :stage],
                           :foreign_key => [:achievement_id, :stage] 
  has_one :next_stage, ->(achievement) { where("next_stages_users_achievements.stage = users_achievements.stage + 1") },
          :class_name => 'Achievement',
          :foreign_key => :achievement_id,
          :primary_key => :achievement_id
  belongs_to :group, :foreign_key => :achievement_id,
                     :primary_key => :achievement_id
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :world
  has_many :changes, :class_name => 'UsersAchievementsChange',
                     :primary_key => :achievement_id
  
  on_deleted_nullify_relation :user
  
  scope :active, -> { where(deleted: false) }
  
  def maxed?
    next_stage.nil?
  end
  
  def needed
    next_stage.try(:needed) || achievement.try(:needed)
  end
end
