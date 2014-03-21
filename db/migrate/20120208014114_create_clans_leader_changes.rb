class CreateClansLeaderChanges < ActiveRecord::Migration
  def up
    create_table :clans_leader_changes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :clan_id, :world_id
      t.integer :leader_id_old, :leader_id_new
      t.datetime :created_at
    end
    
    execute "ALTER TABLE clans_leader_changes ADD PRIMARY KEY(clan_id, world_id, created_at)"
  end
  
  def down
    drop_table :clans_leader_changes
  end
end
