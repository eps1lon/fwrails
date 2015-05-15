class Place < ActiveRecord::Base
  include InvertScope
  
  POS_KEY = [:pos_x, :pos_y]
  FLAGS = [:closed, :save]
  
  # Relations
  belongs_to :area
  has_many :items, through: :items_places
  has_many :items_places, foreign_key: POS_KEY,
                          primary_key: POS_KEY
  has_many :npcs, ->{ order(name: :asc) },
                  foreign_key: POS_KEY,
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
  
  # Unterkünfte
  scope :houses, -> { where(pos_x: (-51000..(-51000 - 499 * 43))).where("pos_y <= ?", -51000) }
  
  # dummyplace
  scope :dummyplace, -> { where(pos_x: -10, pos_y: -9) }
  
  # Minimap around x,y with radius r
  scope :minimap, ->(x, y, r) { where(pos_x: (x - r)..(x + r), pos_y: (y - r)..(y + r)) }

  # line of sight distance from (x,y)
  def los_distance(x, y)
    [self.pos_x - x, self.pos_y - y].max_by { |pos| pos.abs }.abs
  end
  
  def flags
    FLAGS.reject do |r|
      ((self['flags'].to_i || 0) & 2**FLAGS.index(r)).zero?
    end
  end
  
  def flags=(flags)
    self['flags'] = (flags & FLAGS).map { |r| 2**FLAGS.index(r) }.inject(0, :+)
  end
  
  def gfx_path(world)
    "#{world.urls[:images]}/map/#{self.gfx}"
  end
  
  # checks if place is a border place
  def is_border_place?
    self.flags.include?(:closed) && (self.gfx == "black.jpg" || self.gfx == "std.jpg")
  end
  
  # this should return a key that is html valid (for id attr ie)
  def pos_key
    "#{self.pos_x}_#{self.pos_y}"
  end
  
  def self.border_place(x, y)
    # init
    place =  self.new({
      pos_x: x,
      pos_y: y
    })
  
    # closed
    place.flags = [:closed]
    
    #gfx
    if x < 0 # dungeon
      place.gfx = "black.jpg"
    else
      place.gfx = "std.jpg"
    end
    
    #return
    place
  end
end
