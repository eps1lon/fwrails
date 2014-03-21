class CreateUsersNameChanges < ActiveRecord::Migration
  def up
    create_table :users_name_changes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :user_id, :world_id
      t.string :name_old, :name_new
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users_name_changes ADD PRIMARY KEY(user_id, world_id, created_at)"
  end
  
  def down
    drop_table :users_name_changes
  end
end
