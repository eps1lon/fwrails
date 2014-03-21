class CreateUsersExperienceChanges < ActiveRecord::Migration
  def up
    create_table :users_experience_changes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :user_id, :world_id
      t.column :experience, 'INT unsigned'
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users_experience_changes ADD PRIMARY KEY(user_id, world_id, created_at)"
  end
  
  def down
    drop_table :users_experience_changes
  end
end
