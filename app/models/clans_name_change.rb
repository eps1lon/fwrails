class ClansNameChange < ActiveRecord::Base
  include ClanNaming 
  include ClanUrls
  include DeleteMarkable
  include Timestamps
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :clan, :foreign_key => [:clan_id, :world_id]
  belongs_to :world
  
  on_deleted_nullify_relation :clan
  
  scope :active, -> { where(deleted: false) }
  scope :text_ident_like, ->(name) { where("name_old LIKE ? OR name_new LIKE ?", "%#{name}%", "%#{name}%") unless name.nil? }
end