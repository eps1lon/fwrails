class Railpattern < ActiveRecord::Base    
  scope :for_tools, -> { all } 
  attr_accessor :active_pattern
  
  after_initialize do |railpattern|
    @abilities = []
    @concerning_abilities = []
  end
  
  # abilities that are used for certain patterns
  def self.abilities
    # Seelenverbindung Phasenverständnis Selbstheilung
    Ability.where(id: [27, 32, 17])
  end
  
  # method for options_from_collection_for_select
  def name_with_cost
    #TODO number_with_delimiter?
    "#{self.name} (#{self.cost})"
  end
  
  # chance for activation as used ingame
  def chance
    self.chance_float.ceil
  end
  
  # chance from a ability, default = 1
  def ability_factor
    1
  end
 
  # activation chance as float
  def chance_float
    self.ability_factor * @active_pattern.active_factor * self.passive_factor * 100
  end
  
  # active factor for chance
  def active_factor
     self.cost / 10000.0
  end
  
  # passive factor for chance
  def passive_factor
    (0.95 ** (self.cost / 125.0))
  end
  
  def active_magnitude
    
  end
  
  def life_drain
    0
  end
  
  # magnitude of the passive effect
  # 0 for no effect
  def passive_magnitude
    0
  end
  
  def passive_effect
    if @active_pattern.class == self.class then 0 else passive_magnitude end
  end
  
  # final!
  def abilities=(abilities)
    @abilities = abilities.to_a.select { |a| @concerning_abilities.include?(a.id) }
  end
  
  # final!
  def ability(id)
    @abilities.select { |a| a.id == id }.try(:stage) || 0
  end
end
