class UsersAchievementsChange < ActiveRecord::Base
  self.primary_keys = :user_id, :world_id, :achievement_id
  
  belongs_to :achievement, :foreign_key => [:achievement_id]
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :world
  belongs_to :users_achievements, :foreign_key => :achievement_id,
                                  :primary_key => :achievement_id

end
