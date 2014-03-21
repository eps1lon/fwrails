class CreateStockChanges < ActiveRecord::Migration
  def up
    create_table :stock_changes, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :stock_id, :world_id
      t.integer :value, :not_null
      t.datetime :created_at
    end
    
    execute "ALTER TABLE stock_changes ADD PRIMARY KEY(stock_id, world_id, created_at)"
  end
  
  def down
    drop_table :stock_changes
  end
end
