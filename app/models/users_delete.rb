class UsersDelete < ActiveRecord::Base
  include Timestamps
  include UserNaming
  include UserUrls
  self.primary_keys = :user_id, :world_id, :created_at
  
  scope :name_like, ->(name) { where(name: name) unless name.nil? }
  
  belongs_to :world
end