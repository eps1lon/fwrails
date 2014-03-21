class CreateClans < ActiveRecord::Migration
  def up
    create_table :clans, :options => "ENGINE=MyISAM", :id => false do |t|
      t.integer :clan_id,  :world_id
      t.string :name, :tag
      t.integer :leader_id, :coleader_id, :sum_experience, :member_count
      t.datetime :created_at
    end
    
    execute "ALTER TABLE clans ADD PRIMARY KEY (clan_id, world_id)"
    execute "CREATE TABLE clans_old LIKE clans"
  end
  
  def down
    drop_table :clans
    drop_table 'clans_old'
  end
end
