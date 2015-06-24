class Knowdledgeflash < Railpattern
  def chance_float
    100.0
  end
  
  def passive_magnitude
    (0.04 * @active_pattern.cost).ceil.seconds
  end
end
