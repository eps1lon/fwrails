class ClansDelete < ActiveRecord::Base
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :clan, :foreign_key => [:clan_id, :world_id]
  belongs_to :world
  
  def tag
    return self.clan_id.to_s if self['tag'].chop.blank?
    self['tag']
  end
end
