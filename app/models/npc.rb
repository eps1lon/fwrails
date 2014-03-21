class Npc < ActiveRecord::Base
  def place
    Place.where(:pos_x => self.pos_x, :pos_y => self.pos_y)
  end
  
  def place=(place)
    place.pos_x = self.pos_x
    place.pos_y = self.pos_y
  end
end
