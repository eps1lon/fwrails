class CreateMembers < ActiveRecord::Migration
  def change
    create_table :members do |t|
      t.string :name, :null => false
      t.integer :roles, :null => false, :default => 0
      t.timestamps
    end
  end
end
