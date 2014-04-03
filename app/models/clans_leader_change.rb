class ClansLeaderChange < ActiveRecord::Base
  include ClanNaming
  include DeleteMarkable
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :clan, :foreign_key => [:clan_id, :world_id]
  belongs_to :world
  
  has_one :leader_old, :class_name => 'User', 
                       :primary_key => [:leader_id_old, :world_id],
                       :foreign_key => [:user_id, :world_id]
  has_one :leader_new, :class_name => 'User', 
                       :primary_key => [:leader_id_new, :world_id],
                       :foreign_key => [:user_id, :world_id]

  on_deleted_nullify_relation :clan
  
  scope :active, -> { where(deleted: false) }
end
