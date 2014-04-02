class UsersAchievements < ActiveRecord::Base
  include UserNaming
  self.primary_keys = :user_id, :world_id, :achievement_id
  
  belongs_to :achievement, :foreign_key => [:achievement_id, :stage],
                           :primary_key => [:achievement_id, :stage]
  belongs_to :group, :foreign_key => :achievement_id,
                     :primary_key => :achievement_id
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :world
  has_many :changes, :class_name => 'UsersAchievementsChange',
                     :primary_key => :achievement_id
  
  scope :active, -> { where(deleted: false) }
end
