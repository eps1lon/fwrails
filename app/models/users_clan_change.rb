class UsersClanChange < ActiveRecord::Base
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :old_clan, :class_name => 'Clan', 
                        :foreign_key => [:clan_id_old, :world_id]
  belongs_to :new_clan, :class_name => 'Clan', 
                        :foreign_key => [:clan_id_new, :world_id]
  
  alias_method :old, :old_clan
  alias_method :new, :new_clan
                       
  # belongs_to :user, :conditions => {:self => {:deleted => false}}
  alias_method :__user, :user
  def user
    self.__user if !self.deleted
  end
end
