class Dump < ActiveRecord::Base
  scope :public, -> { where(public: true) }
  
  def children
    return system_path.children(false) if subs?
    [nil]
  end
  
  def files
    children.map { |f| web_path(f) }
  end
  
  def updated_at
    return system_path.mtime unless subs?
    children.map { |f| system_path.join(f) }.map(&:mtime).max
  end
  
  def path
    Pathname.new(super)
  end
  
  def subs?
    system_path.directory?
  end
  
  def web_path(file = nil)
    unless file.nil?
      return path.join(file)
    end
    path
  end
  
  private   
  
  def system_path
    Rails.root.join("public", "dumps", path)
  end
end
