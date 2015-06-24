class Healingtwine < Railpattern
  after_initialize do
    @concerning_abilities << 17
  end
  
  def chance_float
    100.0
  end
  
  # TODO: Abhängigkeit Selbstheilung
  def passive_magnitude
    # (0.1..0.4) * @active_pattern.cost
    ((0.1 * @active_pattern.cost).ceil..(0.4 * @active_pattern.cost).ceil)
  end
end
