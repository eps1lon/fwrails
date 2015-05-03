class AddHuntToNpcs < ActiveRecord::Migration
  def change
    create_table :drops_npcs, options: "ENGINE=MyISAM", id: false do |t|
      t.integer :drop_id, :npc_id
      t.float :chance, default: 0.0
      t.timestamps
    end
    
    change_table :npcs do |t|
      t.integer :gold
    end
  end
  
  def up
    execute "ALTER TABLE drops_npcs ADD PRIMARY KEY(drop_id, npc_id)"
  end
end
