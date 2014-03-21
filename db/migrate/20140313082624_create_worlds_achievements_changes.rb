class CreateWorldsAchievementsChanges < ActiveRecord::Migration
  def change
    create_table :worlds_achievements_changes, 
                 :options => "ENGINE=MyISAM", 
                 :id => false do |t|
      t.integer :achievement_id, :world_id, :progress
      t.datetime :created_at
    end
    
    execute "ALTER TABLE worlds_achievements_changes ADD PRIMARY KEY "+
            "(achievement_id, world_id, created_at)"
  end
end
