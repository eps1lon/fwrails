class CreateClansDeletes < ActiveRecord::Migration
  def up
    create_table :clans_deletes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer  :clan_id, :world_id
      t.string   :tag
      t.datetime :created_at
    end
    
    execute "ALTER TABLE clans_deletes ADD PRIMARY KEY (clan_id, world_id, created_at)"
  end
  
  def down
    drop_table :clans_deletes
  end
end
