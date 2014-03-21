class CreateCategories < ActiveRecord::Migration
  def change
    create_table :categories, :options => "ENGINE=MyISAM" do |t|
      t.string :name
      t.integer :type
      t.timestamps
    end
  end
end
