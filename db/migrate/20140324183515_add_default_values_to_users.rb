class AddDefaultValuesToUsers < ActiveRecord::Migration
  def up
    change_column :users, :clan_id, :integer, :null => true, :default => nil
    change_column_default :users, :experience, 0
    change_column_default :users, :name, ''
    change_column :users, :race_id, :integer, :null => true, :default => nil
  end
end
