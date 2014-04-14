class UsersNew < ActiveRecord::Base
  include DeleteMarkable
  include Timestamps
  include UserNaming
  include UserUrls
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :user, :class_name => 'User', :foreign_key => [:user_id, :world_id]
  belongs_to :world
  
  scope :active, -> { where(deleted: false) }
  
  on_deleted_nullify_relation :user
end
