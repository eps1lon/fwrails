class UsersNew < ActiveRecord::Base
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :user, :class_name => 'User', :foreign_key => [:user_id, :world_id]
  belongs_to :world
  
  
  # belongs_to :user, :conditions => {:self => {:deleted => false}}
  alias_method :__user, :user
  def user
    self.__user if !self.deleted
  end
end
