class NpcsMember < ActiveRecord::Base
  include Enum
  belongs_to :member
  belongs_to :npc
end
