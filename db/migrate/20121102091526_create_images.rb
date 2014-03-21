class CreateImages < ActiveRecord::Migration
  def change
    create_table :images, :options => "ENGINE=MyISAM" do |t|
      t.integer :category_id
      t.string :filename
      t.timestamps
    end
  end
end
