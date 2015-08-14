class Cellvibe < Railpattern
  def cost(standtime = 0)
    self['cost'] + standtime * 8
  end
  
  def life_drain
    0.10
  end
  
  def passive_magnitude
    1
  end
end
