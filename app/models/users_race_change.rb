class UsersRaceChange < ActiveRecord::Base
  include DeleteMarkable
  include Timestamps
  include UserNaming
  include UserUrls
  self.primary_keys = :user_id, :world_id, :created_at
  
  belongs_to :world
  belongs_to :user, :foreign_key => [:user_id, :world_id]
  belongs_to :old_race, :class_name => 'Race',
                        :foreign_key => :race_id_old
  belongs_to :new_race, :class_name => 'Race',
                        :foreign_key => :race_id_new
  
  on_deleted_nullify_relation :user
  
  # alias relation
  alias_method :old, :old_race
  alias_method :new, :new_race
  
  scope :active, -> { where(deleted: false) }
  scope :race, ->(race) { where("race_id_old = ? OR "+
                                "race_id_new = ?", race.id, race.id) unless race.nil?}
end
