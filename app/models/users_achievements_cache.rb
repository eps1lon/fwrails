class UsersAchievementsCache < ActiveRecord::Base
  alias_attribute :updated_at, :created_at
  
  self.primary_keys = :user_id, :world_id
  
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :world
  
  has_many :achievements, -> { where(:deleted => false) },
           :class_name => 'UsersAchievements',
           :foreign_key => [:user_id, :world_id]
  
  def self.last_update
    self.first.updated_at
  end
end
