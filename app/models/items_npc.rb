class ItemsNpc < ActiveRecord::Base
  belongs_to :item
  belongs_to :member
  belongs_to :npc
  
  enum action: {kill: 1, chase: 2}
  
  # TODO: Global Drops
  #scope :global_drops, -> { where(1) }
  
  # TODO: Drops with variable name, gold
end
