class CreateUsersClanChanges < ActiveRecord::Migration
  def up
    create_table :users_clan_changes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :user_id, :world_id
      t.integer :clan_id_old, :clan_id_new
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users_clan_changes ADD PRIMARY KEY(user_id, world_id, created_at)"
  end
  
  def down
    drop_table :users_clan_changes
  end
end
