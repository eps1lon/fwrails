class Area < ActiveRecord::Base
  has_many :places
  
  def dungeon?
    !(self.places.where("pos_x < 0 OR pos_y < 0").empty?)
  end
end
