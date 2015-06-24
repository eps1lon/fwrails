class Lifeincision < Railpattern
  def chance_float
    100.0
  end
  
  def passive_magnitude
    @active_pattern.class == Lifewedge ? 20 : 10
  end
end
