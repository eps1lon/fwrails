class Hash
  def compact
    self.delete_if { |_,v| v.blank? }
  end
  
  def only(*keys)
    keep_if { |key,_| keys.flatten.include?(key) }
  end
end