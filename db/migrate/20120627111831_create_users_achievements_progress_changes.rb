class CreateUsersAchievementsProgressChanges < ActiveRecord::Migration
  def up
    create_table :users_achievements_progress_changes, :options => "ENGINE=MyISAM", :id => false do |t|
      t.integer :user_id, :world_id, :achievement_group, :progress
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users_achievements_progress_changes ADD PRIMARY KEY (user_id, world_id, achievement_group, created_at)"
  end
  
  def down
    drop_table :users_achievements_progress_changes
  end
end
