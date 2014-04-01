class ClansColeaderChange < ActiveRecord::Base
  include ClanNaming
  self.primary_keys = :clan_id, :world_id, :created_at
  
  belongs_to :clan, :foreign_key => [:clan_id, :world_id]
  belongs_to :world

  has_one :coleader_old, :class_name => 'User', 
                       :primary_key => [:coleader_id_old, :world_id],
                       :foreign_key => [:user_id, :world_id]
  has_one :coleader_new, :class_name => 'User', 
                         :primary_key => [:coleader_id_new, :world_id],
                         :foreign_key => [:user_id, :world_id]
end
