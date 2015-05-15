class AddUniqueIndexNameToAreas < ActiveRecord::Migration
  def change
    change_table :areas do |t|
      t.index :name, unique: true
    end
  end
end
