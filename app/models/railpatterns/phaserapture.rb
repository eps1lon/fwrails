class Phaserapture < Railpattern
  after_initialize do
    @concerning_abilities << 32
  end
  
  def ability_factor
    1.0075 ** ability(32)
  end
  
  def passive_magnitude
    1
  end
end