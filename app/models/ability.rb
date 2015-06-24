class Ability < ActiveRecord::Base
  after_initialize :default_values

  scope :for_calc, -> { order("id = 3").order(:name) }
  
  def learntime(stage, lt = 0)
    (self.basetime * self.lt_factor(lt) * (stage - 1)).floor
  end
  
  def learntime_till(from_stage, till_stage, lt = 0)    
    if from_stage > till_stage || till_stage == 1
      0 
    else
      #                                       gaussian sum
      (self.basetime * lt_factor(lt) * 
      # gaussian: sum_(i=1)^n = (n^2 + n) / 2
      # sum_(i=from_stage+1)^till_stage i = sum_(i=1)^till_stage - sum_(i=1)^(from_stage+1) 
      (((till_stage - 1) ** 2 + till_stage - (from_stage - 1) ** 2 - from_stage) / 2)).floor
    end  
  end
  
  def lt_factor(stage)
    # lerntechnik hat keinen einfluss auf sich selbst
    self.id == 3 ? 1 : Ability.lt_factor(stage)
  end
  
  def self.lt_factor(stage)
    0.99 ** stage
  end
  
  # get first ability from a collection by prop
  def self.get_ability_by(prop, value, collection)
    collection.to_a.select { |a| a[prop] == value}[0]
  end
  
  # returns a new id if we need one
  def self.new_id(old_id, collection_hash)
    old_id || ((collection_hash.keys << 0).min - 1)
  end
  
  private
  def default_values
    self.name ||= "ability(id=#{self.id})"
    self.desc ||= ""
  end
end
