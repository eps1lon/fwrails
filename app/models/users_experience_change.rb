class UsersExperienceChange < ActiveRecord::Base
  include DeleteMarkable
  include UserNaming
  include UserUrls
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  
  on_deleted_nullify_relation :user
end
