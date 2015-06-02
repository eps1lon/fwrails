class Npc < ActiveRecord::Base
  include InvertScope
  
  belongs_to :place, foreign_key: [:pos_x, :pos_y],
                     primary_key: [:pos_x, :pos_y]
  enum unique_npc: {
    npc: 1, 
    unique_npc: 2, 
    group_npc: 3, 
    unknown: 4, 
    passiv: 5, 
    resistance: 6, 
    superresistance: 7
  }
  
  has_many :drops, -> { where(items_npcs: {action: ItemsNpc.actions[:kill]}) }, 
                   class_name: "ItemsNpc"
  
  scope :in_range, ->(x, y, r) { where(pos_x: (x - r)..(x + r), pos_y: (y - r)..(y + r)) }
  scope :in_ressurect_range, ->(x, y) { in_rage(x, y, 2) }
  
  scope :bloodnpcs, -> { where(unique_npc: [Npc.unique_npcs[:unique_npc], Npc.unique_npcs[:group_npc]]).
                         where(table[:id].lt(0)).
                         where(Arel::Nodes::NamedFunction.new("LOCATE", ['-', table[:name]]).gt(0)) }
  
  scope :dancers, -> { where(table[:id].lt(0)).
                       where(table[:name].matches("Tänzerin von %")) }
  
  scope :killable, -> { where.not(unique_npc: Npc.unique_npcs[:passiv]) }
  
  scope :servants, -> { where(table[:id].lt(0)).
                        where(table[:name].matches("Diener von %")) }
  
  scope :shadowcreatures, -> { where(table[:id].lt(0)).
                               where(table[:name].matches("Schattenkreatur %"))}
  
  scope :slmania_npcs, -> { persistent_npcs.killable.
                            group(:name).reorder(:name) }
  
  scope :persistent_npcs, -> { invert(:bloodnpcs).
                               invert(:shadowcreatures).
                               invert(:dancers).
                               invert(:servants).
                               order(:id)
                               }
  
  def self.table
    Npc.arel_table
  end
  
  FLAGS = [:aggressive]
  
  def flags
    FLAGS.reject do |r|
      ((self['flags'].to_i || 0) & 2**FLAGS.index(r)).zero?
    end
  end
  
  def flags=(flags)
    self['flags'] = (flags & FLAGS).map { |r| 2**FLAGS.index(r) }.inject(0, :+)
  end
  
  def self.letters
    letter = "substr(upper(name),1,1)"
    select("#{letter} as letter").group(letter).collect(&:letter).sort
  end
end
