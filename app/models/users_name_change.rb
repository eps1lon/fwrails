class UsersNameChange < ActiveRecord::Base
  include UserNaming
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :user, :foreign_key => [:user_id, :world_id] 
  
  scope :active, -> { where(deleted: false) }
  
  def old
    {:name => self.name_old}
  end
  
  def new
    {:name => self.name_new}
  end
  
  # belongs_to :user, :conditions => {:self => {:deleted => false}}
  alias_method :__user, :user
  def user
    self.__user if !self.deleted
  end
end
