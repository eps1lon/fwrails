class CreateUsers < ActiveRecord::Migration
  def up
    create_table :users, :options => "ENGINE=MyISAM", :id => false do |t|
      t.integer :user_id,  :world_id, :clan_id, :race_id
      t.string :name
      t.integer :experience
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users ADD PRIMARY KEY (user_id, world_id)"
    execute "CREATE TABLE users_old LIKE users"
  end
  
  def down
    drop_table :users
    drop_table 'users_old'
  end
end
