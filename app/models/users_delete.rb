class UsersDelete < ActiveRecord::Base
  include UserNaming
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :world
end