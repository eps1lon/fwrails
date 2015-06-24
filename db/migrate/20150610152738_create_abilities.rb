class CreateAbilities < ActiveRecord::Migration
  def change
    create_table :abilities do |t|
      t.string :name, :null => false
      t.text :desc, :null => false
      t.integer :basetime, :default => 0, :null => false
      t.integer :max_stage, :default => 1, :null => false
      t.timestamps
    end
  end
end
