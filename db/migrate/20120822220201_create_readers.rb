class CreateReaders < ActiveRecord::Migration
  def change
    create_table :readers do |t|
      t.string :email, :null => false
      t.string :name, :null => false
      t.timestamps
    end
  end
end
