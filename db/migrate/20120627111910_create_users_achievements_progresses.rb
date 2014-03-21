class CreateUsersAchievementsProgresses < ActiveRecord::Migration
  def up
    create_table :users_achievements_progresses, :options => "ENGINE=MyISAM", :id => false do |t|
      t.integer :user_id, :world_id, :achievement_group, :progress
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users_achievements_progresses ADD PRIMARY KEY (user_id, world_id, achievement_group)"
    execute "CREATE TABLE users_achievements_progresses_old LIKE users_achievements_progresses"
  end
  
  def down
    drop_table :users_achievements_progresses
    drop_table "users_achievements_progresses_old"
  end
end
