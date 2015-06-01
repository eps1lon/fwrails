class CreateRailpatterns < ActiveRecord::Migration
  def change
    create_table :railpatterns do |t|
      t.string :name
      t.text :desc
      t.string :gfx, :default => nil
      t.integer :cost
      t.string :klass, :default => 'Railpattern', :nil => false
      t.timestamps
    end
  end
end
