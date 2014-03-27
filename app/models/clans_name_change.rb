class ClansNameChange < ActiveRecord::Base
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :clan, :foreign_key => [:clan_id, :world_id]
  belongs_to :world
end