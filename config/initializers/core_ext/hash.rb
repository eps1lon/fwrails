class Hash
  def compact
    self.delete_if { |_,v| v.blank? }
  end
end