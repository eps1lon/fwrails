class ItemsNpc < ActiveRecord::Base
  include Enum # remove in Rails 4.1
  belongs_to :item
  belongs_to :member
  belongs_to :npc
  
  enum action: [:kill, :chase]
end
