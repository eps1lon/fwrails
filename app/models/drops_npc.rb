class DropsNpc < ActiveRecord::Base
  self.primary_keys = :npc_id, :drop_id
  
  belongs_to :item, primary_key: :drop_id
  belongs_to :npc
end
