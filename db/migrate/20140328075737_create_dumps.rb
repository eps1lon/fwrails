class CreateDumps < ActiveRecord::Migration
  def change
    create_table :dumps do |t|
      t.boolean :public, :default => true
      t.string :name, :default => nil
      t.string :path, :default => nil
    end
  end
end
