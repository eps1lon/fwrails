class CreateUsersAchievements < ActiveRecord::Migration
  def up
    create_table :users_achievements, :options => "ENGINE=MyISAM", :id => false, :force => true do |t|
      t.integer :achievement_id, :user_id, :world_id
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users_achievements ADD PRIMARY KEY (achievement_id, user_id, world_id)"
  end
  
  def down
    drop_table :users_achievements
  end
end
