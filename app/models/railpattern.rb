class Railpattern < ActiveRecord::Base    
  scope :for_tools, -> { all } 
  
  # abilities that are used for certain patterns
  def self.abilities
    # Seelenverbindung Phasenverständnis
    %w{1 2}
  end
  
  # method for options_from_collection_for_select
  def name_with_cost
    #TODO number_with_delimiter?
    "#{self.name} (#{self.cost})"
  end
  
  # chance for activation as used ingame
  def chance(active_pattern)
    self.chance_float(active_pattern).ceil
  end
  
  # chance from a chara, default = 1
  def chara_factor(stage)
    1
  end
 
  # activation chance as float
  def chance_float(active_pattern)
    self.chara_factor * active_pattern.active_factor * self.passive_factor
  end
  
  # active factor for chance
  def active_factor
     self.cost / 10000.0
  end
  
  # passive factor for chance
  def passive_factor
    0.95 ** (self.cost / 125.0)
  end
end
