class Spiritlite < Railpattern
  after_initialize do
    @concerning_abilities << 27
  end
  
  def ability_factor
    1.015 ** ability(27)
  end
  
  def passive_magnitude
    1
  end
end