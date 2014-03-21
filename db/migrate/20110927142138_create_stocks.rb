class CreateStocks < ActiveRecord::Migration
  def change
    create_table :stocks, :options => "ENGINE=MyISAM" do |t|
      t.string :name
      t.timestamps
    end
  end
end
