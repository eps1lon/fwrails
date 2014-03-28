class Dump < ActiveRecord::Base
  def files
    files = [nil]
    files = Dir.open(system_path).each.reject { |dir| %w{. ..}.include?(dir) } if self.subs?

    files.map { |f| web_path(f) }
  end
  
  def updated_at
    File.stat(system_path).mtime
  end
  
  def public?
    !self['public'].zero?
  end
  
  def subs?
    File.directory?(system_path)
  end
  
  def web_path(file = nil)
    unless file.nil?
      return File.join(self.path, file)
    end
    self.path
  end
  
  def self.public
    where(:public => true)
  end
  
  private   
  
  def system_path
    Rails.root.join("public", "dumps", self.path)
  end
end
