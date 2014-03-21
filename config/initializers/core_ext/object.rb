# extracted from http://www.pixellatedvisions.com/2009/06/22/string-is-a-number
class Object
  def is_numeric?
    true if Float(self) rescue false
  end
end