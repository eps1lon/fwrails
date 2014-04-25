class AddSlmania < ActiveRecord::Migration
  def up
    options = "ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci"
    
    # previously created
    [:npcs, :places].each do |table|
      drop_table table if ActiveRecord::Base.connection.table_exists? table
    end
    
    create_table :areas, :options => options do |t|
      t.string :name
      t.integer :type, :default => 0, :null => false
      t.timestamps
    end
    
    create_table :items, :options => options do |t|
      t.string :name
      t.timestamps
    end
    
    create_table :items_npcs, :id => false, :options => options do |t|
      t.integer :item_id
      t.integer :npc_id
      t.integer :member_id
      t.integer :count
      t.integer :action, :default => 0, :null => false
      t.timestamps
    end
    execute "ALTER TABLE items_npcs ADD PRIMARY KEY (item_id, npc_id, member_id, action)"
    
    create_table :items_places, :id => false, :options => options do |t|
      t.integer :item_id
      t.integer :pos_x
      t.integer :pos_y
      t.integer :count
      t.timestamps
    end
    execute "ALTER TABLE items_places ADD PRIMARY KEY (item_id, pos_x, pos_y)"
    
    create_table :npcs, :options => options do |t|
      t.string :name
      t.text :description
      t.integer :strength, :default => nil
      t.integer :live, :default => nil
      t.integer :pos_x, :default => -10, :null => false
      t.integer :pos_y, :default => -9, :null => false
      t.integer :unique_npc, :default => 0, :null => false
      t.integer :flags, :default => 0, :null => false
      t.timestamps
    end
    add_index :npcs, [:pos_x, :pos_y]
    
    create_table :npcs_members, id: false, options: options do |t|
      t.integer :npc_id
      t.integer :member_id
      t.integer :chasecount, default: 0, null: false
      t.integer :killcount, default: 0, null: false
      t.timestamps
    end
    add_index :npcs_members, [:npc_id, :member_id], unique: true
    
    create_table :places, :options => options do |t|
      t.string :name
      t.text :desc
      t.string :gfx
      t.integer :pos_x
      t.integer :pos_y
      t.integer :flags, :default => 0, :null => false
      t.integer :area_id
      t.timestamps
    end
    add_index :places, :area_id
    add_index :places, [:pos_x, :pos_y], :unique => true
    
    create_table :places_nodes, :id => false, :options => options do |t|
      t.integer :entry_pos_x
      t.integer :entry_pos_y
      t.integer :exit_pos_x
      t.integer :exit_pos_y
      t.string :via
      t.timestamps
    end
    add_index :places_nodes, [:entry_pos_x, :entry_pos_y], name: 'by_entry'
    add_index :places_nodes, [:exit_pos_x, :exit_pos_y], name: 'by_exit'
    add_index :places_nodes, [:entry_pos_x, :entry_pos_y, :exit_pos_x, :exit_pos_y], name: 'unique_node', unique: true
  
    change_table :members do |t|
      t.string :authenticity_token
    end
  end
  
  def down
    [:areas, :items, :items_npcs, :items_places, :npcs, :npcs_members, :places, :places_nodes].each do |table|
      drop_table table if ActiveRecord::Base.connection.table_exists? table
    end
    
    remove_column :members, :authenticity_token
  end
end
