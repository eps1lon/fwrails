class CreateClansTagChanges < ActiveRecord::Migration
  def up
    create_table :clans_tag_changes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :clan_id, :world_id
      t.string :tag_old, :tag_new
      t.datetime :created_at
    end
    
    execute "ALTER TABLE clans_tag_changes ADD PRIMARY KEY(clan_id, world_id, created_at)"
  end
  
  def down
    drop_table :clans_tag_changes
  end
end
