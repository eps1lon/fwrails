class Place < ActiveRecord::Base
  belongs_to :area
  
  def npcs
    Npc.where(:pos_x => self.pos_x, :pos_y => self.pos_y)
  end
  
  def npcs=(npcs)
    npcs.pos_x = self.pos_x
    npcs.pos_y = self.pos_y
  end
end
