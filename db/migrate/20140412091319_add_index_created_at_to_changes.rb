class AddIndexCreatedAtToChanges < ActiveRecord::Migration
  def change
    [:users_clan_changes, :users_deletes, :users_news, :users_name_changes, 
     :users_race_changes, :clans_coleader_changes, :clans_deletes, 
     :clans_leader_changes, :clans_name_changes, :clans_news, 
     :clans_tag_changes, :users_achievements_changes, 
     :worlds_achievements_changes].each do |t|
      add_index t, :created_at
    end
  end
end
