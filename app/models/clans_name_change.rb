class ClansNameChange < ActiveRecord::Base
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :clan
end