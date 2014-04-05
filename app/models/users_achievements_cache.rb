class UsersAchievementsCache < ActiveRecord::Base
  include DeleteMarkable
  include UserNaming
  include UserUrls
  alias_attribute :updated_at, :created_at
  
  self.primary_keys = :user_id, :world_id
  
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :world
  
  has_many :achievements, -> { where(:deleted => false) },
           :class_name => 'UsersAchievements',
           :foreign_key => [:user_id, :world_id]

  on_deleted_nullify_relation :user
  
  scope :active, -> { where(deleted: false) }
  
  def self.last_update
    self.take.updated_at
  end
end
