class Guardbreaker < Railpattern
  def chance_float
    if @active_pattern.nil?
      [0, 100]
    else
      @active_pattern.cost >= 500 ? 100.0 : 0.0
    end
  end
  
  def passive_magnitude
    1
  end
end
