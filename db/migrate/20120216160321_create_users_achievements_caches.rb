class CreateUsersAchievementsCaches < ActiveRecord::Migration
  def up
    create_table :users_achievements_caches, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :user_id, :world_id
      t.integer :count, :reward_collected
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users_achievements_caches ADD PRIMARY KEY(user_id, world_id)"
  end
  
  def down
    drop_table :users_achievements_caches
  end
end
