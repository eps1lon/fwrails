class CreateStatistics < ActiveRecord::Migration
  def change
    create_table :statistics, :options => "ENGINE=MyISAM" do |t|
      t.string :name
      t.timestamps
    end
  end
end
