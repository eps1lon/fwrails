class ClansTagChange < ActiveRecord::Base
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :clan
  
  def tag_new
    self.tag('tag_new')
  end
  
  def tag_old
    self.tag('tag_old')
  end
  
  def tag(prop)
    return self.clan_id if self[prop].empty?
    self[prop]
  end
end