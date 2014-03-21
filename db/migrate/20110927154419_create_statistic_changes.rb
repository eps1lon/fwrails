class CreateStatisticChanges < ActiveRecord::Migration
  def up
    create_table :statistic_changes, :id => false, :options => "ENGINE=MyISAM", :force => true do |t|
      t.integer :statistic_id, :world_id
      t.integer :value, :not_null => true, :default => 0, :limit => 8, :precision => :unsigned
      t.datetime :created_at
    end
    
    execute "ALTER TABLE statistic_changes ADD PRIMARY KEY(statistic_id, world_id, created_at)"
  end
  
  def down
    drop_table :statistic_changes
  end
end
