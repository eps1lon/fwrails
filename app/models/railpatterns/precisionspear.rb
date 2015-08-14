class Precisionspear < Railpattern
  after_initialize do
    @concerning_abilities << 36
  end
  
  def life_drain
    500
  end
  
  # fetch max_stage
  def passive_magnitude
    ability(36) >= 80 ? 0.10 : 0.08
  end
  
  def chance_float
    100.0
  end
end
