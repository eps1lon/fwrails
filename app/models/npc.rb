class Npc < ActiveRecord::Base
  belongs_to :place, foreign_key: [:pos_x, :pos_y],
                     primary_key: [:pos_x, :pos_y]
  
  
  scope :slmania_list, -> { where.not(Slmania::Conditions.bloodnpcs[:all]).
                            where.not(Slmania::Conditions.shadowcreatures[:all]).
                            group(:name).order(:name) }
  
  FLAGS = [:aggressive]
  UNIQUE_NPC = {
    1 => :npc,
    2 => :unique_npc,
    3 => :group_npc,
    4 => :unknown,
    5 => :passiv,
    6 => :resistence,
    7 => :superresistence
  }
  
  def flags
    FLAGS.reject do |r|
      ((self['flags'].to_i || 0) & 2**FLAGS.index(r)).zero?
    end
  end
  
  def flags=(flags)
    self['flags'] = (flags & FLAGS).map { |r| 2**FLAGS.index(r) }.inject(0, :+)
  end
  
  def unique_npc
    UNIQUE_NPC[self['unique_npc']]
  end
  
  def unique_npc=(unique_npc)
    self['unique_npc'] = UNIQUE_NPC.index_at(unique_npc) || 4
  end
  
  def self.letters
    letter = "substr(upper(name),1,1)"
    select("#{letter} as letter").group(letter).collect(&:letter).sort
  end
end
