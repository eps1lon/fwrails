class CreateClansColeaderChanges < ActiveRecord::Migration
  def up
    create_table :clans_coleader_changes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :clan_id, :world_id
      t.integer :coleader_id_old, :coleader_id_new
      t.datetime :created_at
    end
    
    execute "ALTER TABLE clans_coleader_changes ADD PRIMARY KEY(clan_id, world_id, created_at)"
  end
  
  def down
    drop_table :clans_coleader_changes
  end
end
