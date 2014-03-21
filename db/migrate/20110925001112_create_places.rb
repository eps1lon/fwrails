class CreatePlaces < ActiveRecord::Migration
  def change
    create_table :places, :options => "ENGINE=MyISAM" do |t|

      t.timestamps
    end
  end
end
