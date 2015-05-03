class Place < ActiveRecord::Base
  POS_KEY = [:pos_x, :pos_y]
  
  belongs_to :area
  has_many :items, through: :items_places
  has_many :items_places, foreign_key: POS_KEY,
                          primary_key: POS_KEY
  has_many :npcs, foreign_key: POS_KEY,
                  primary_key: POS_KEY
  has_many :access_places, class_name: 'PlacesNode',
                   foreign_key: [:exit_pos_x, :exit_pos_y],
                   primary_key: POS_KEY do
    alias_attribute :pos_x, :exit_pos_x
    alias_attribute :pos_y, :exit_pos_y
  end
  has_many :nodes, class_name: 'PlacesNode',
                   foreign_key: [:entry_pos_x, :entry_pos_y],
                   primary_key: POS_KEY do
    alias_attribute :pos_x, :exit_pos_x
    alias_attribute :pos_y, :exit_pos_y
  end

  def gfx_path
    "http://welt1.freewar.de/freewar/images/map/#{self.gfx}"
  end
end
