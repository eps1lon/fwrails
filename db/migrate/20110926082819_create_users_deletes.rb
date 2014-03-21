class CreateUsersDeletes < ActiveRecord::Migration
  def up
    create_table :users_deletes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer  :user_id, :world_id
      t.string   :name
      t.datetime :created_at
    end
    
    execute "ALTER TABLE users_deletes ADD PRIMARY KEY(user_id, world_id, created_at)"
  end
  
  def down
    drop_table :users_deletes
  end
end
