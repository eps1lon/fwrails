class PlacesNode < ActiveRecord::Base
  POS_KEY = [:pos_x, :pos_y]
  
  has_many :entries, class_name: 'Place',
                     foreign_key: POS_KEY,
                     primary_key: [:entry_pos_x, :entry_pos_y]
  has_many :exits, class_name: 'Place',
                   foreign_key: POS_KEY,
                   primary_key: [:exit_pos_x, :exit_pos_y]
end
