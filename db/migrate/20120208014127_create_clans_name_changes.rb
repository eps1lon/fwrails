class CreateClansNameChanges < ActiveRecord::Migration
  def up
    create_table :clans_name_changes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :clan_id, :world_id
      t.string :name_old, :name_new
      t.datetime :created_at
    end
    
    execute "ALTER TABLE clans_name_changes ADD PRIMARY KEY(clan_id, world_id, created_at)"
  end
  
  def down
    drop_table :clans_name_changes
  end
end
