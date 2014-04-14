class UsersNameChange < ActiveRecord::Base
  include DeleteMarkable
  include Timestamps
  include UserNaming
  include UserUrls

  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :user, :foreign_key => [:user_id, :world_id] 
  
  on_deleted_nullify_relation :user
  
  scope :active, -> { where(deleted: false) }
  scope :name_like, ->(name) { where("name_old LIKE ? OR name_new LIKE ?", "%#{name}%", "%#{name}%") unless name.nil? }
  
  def old
    {:name => self.name_old}
  end
  
  def new
    {:name => self.name_new}
  end
end
