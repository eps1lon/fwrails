class ClansNameChange < ActiveRecord::Base
  include ClanNaming 
  include DeleteMarkable
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :clan, :foreign_key => [:clan_id, :world_id]
  belongs_to :world
  
  on_deleted_nullify_relation :clan
  
  scope :active, -> { where(deleted: false) }
end