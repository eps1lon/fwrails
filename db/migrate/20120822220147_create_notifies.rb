class CreateNotifies < ActiveRecord::Migration
  def change
    create_table :notifies do |t|
      t.string :class_name
      t.string :sender
      t.string :text
      t.timestamps
    end
  end
end
