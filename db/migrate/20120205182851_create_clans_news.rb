class CreateClansNews < ActiveRecord::Migration
  def up
    create_table :clans_news, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer  :clan_id, :world_id
      t.string   :tag
      t.datetime :created_at
    end
    
    execute "ALTER TABLE clans_news ADD PRIMARY KEY (clan_id, world_id, created_at)"
  end
  
  def down
    drop_table :clans_news
  end
end
