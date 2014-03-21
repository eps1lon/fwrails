class CreateExtensions < ActiveRecord::Migration
  def change
    create_table :extensions do |t|
      t.string :name
      t.text :desc
      t.string :filename
      t.integer :rating
      t.integer :ratings, :null => false, :default => 0
      t.integer :downloads, :null => false, :default => 0
      t.timestamps
    end
  end
end
