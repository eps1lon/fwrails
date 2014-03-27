class CreateNews < ActiveRecord::Migration
  def change
    create_table :news do |t|
      t.string :heading
      t.text :content
      t.integer :member_id
      t.datetime :publish_at, :nil => false
      t.timestamps
    end
  end
end
