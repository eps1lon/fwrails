class Npc < ActiveRecord::Base
  belongs_to :place, foreign_key: [:pos_x, :pos_y],
                     primary_key: [:pos_x, :pos_y]
  enum unique_npc: {npc: 1, unique_npc: 2, group_npc: 3, unknown: 4, passiv: 5, resistance: 6, superresistance: 7}
  
  has_many :drops, -> { where(items_npcs: {action: ItemsNpc.actions[:kill]}) }, 
                   class_name: "ItemsNpc"
  
  scope :in_range, ->(x, y, r) { where(pos_x: (x - r)..(x + r), pos_y: (y - r)..(y + r)) }
  scope :in_ressurect_range, ->(x, y) { in_rage(x, y, 2) }
  
  scope :slmania_list, -> { where.not(Npc::Conditions.bloodnpcs[:all]).
                            where.not(Npc::Conditions.shadowcreatures[:all]).
                            group(:name).order(:name) }
  
  scope :persistent_npcs, -> { where.not(Npc::Conditions.bloodnpcs[:all]).
                               where.not(Npc::Conditions.shadowcreatures[:all]).
                               where.not(Npc::Conditions.dancer).
                               where.not(Npc::Conditions.servant).
                               order(:id)
                               }
  
  module Conditions
    def self.table
      Npc.arel_table
    end
    
    def self.bloodnpcs
      npcs = Npc.arel_table
      {
        :all => npcs[:id].lt(0).
                and(npcs[:unique_npc].gt(1)).
                and(Arel::Nodes::NamedFunction.new("LOCATE", ['-', npcs[:name]]).gt(0))   
      }
    end
    
    def self.shadowcreatures
      npcs = Npc.arel_table
      types = {all: npcs[:id].lt(0).and(npcs[:name].matches("Schattenkreatur %"))}
      Npc.group(:unique_npc).each do |npc|
        types[npc.unique_npc] = types[:all].and(npcs[:unique_npc].eq(npc.unique_npc))
      end
      types
    end
    
    def self.dancer
      table[:id].lt(0).and(table[:name].matches("Tänzerin von %"))
    end
    
    def self.servant
      table[:id].lt(0).and(table[:name].matches("Diener von %"))
    end
    
    def self.npc(name)
      {name => Npc.arel_table[:name].eq(name)}
    end
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
