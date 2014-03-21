class AddDeletedFlag < ActiveRecord::Migration
  def change
    [:clans_coleader_changes, :clans_leader_changes, :clans_name_changes, 
     :clans_tag_changes, :users_achievements, :users_clan_changes, 
     :users_experience_changes, :users_name_changes, :users_race_changes].each do |table|
      add_column table, :deleted, :boolean, :default => 0, :null => false
    end
  end
end
