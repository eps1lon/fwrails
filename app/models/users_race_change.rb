class UsersRaceChange < ActiveRecord::Base
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :old_race, :class_name => 'Race',
                        :foreign_key => :race_id_old
  belongs_to :new_race, :class_name => 'Race',
                        :foreign_key => :race_id_new
                       
  alias_method :old, :old_race
  alias_method :new, :new_race
end
