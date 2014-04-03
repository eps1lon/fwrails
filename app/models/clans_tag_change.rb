class ClansTagChange < ActiveRecord::Base
  include ClanNaming
  include ClanUrls
  include DeleteMarkable
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :clan, :foreign_key => [:clan_id, :world_id]
  belongs_to :world
  
  on_deleted_nullify_relation :clan
  
  scope :active, -> { where(deleted: false) }
  
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