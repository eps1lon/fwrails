class UsersClanChange < ActiveRecord::Base
  include DeleteMarkable
  include UserNaming
  include UserUrls
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :old_clan, :class_name => 'Clan', 
                        :foreign_key => [:clan_id_old, :world_id]
  belongs_to :new_clan, :class_name => 'Clan', 
                        :foreign_key => [:clan_id_new, :world_id]
  
  # alias relation
  alias_method :old, :old_clan
  alias_method :new, :new_clan
  
  on_deleted_nullify_relation :user
  
  scope :active, -> { where(deleted: false) }
end
