class CreateRaces < ActiveRecord::Migration
  def change
    create_table :races, :options => "ENGINE=MyISAM" do |t|
      t.string :name
      t.integer :base_live, :base_strength, :base_intelligence
      t.integer :place_id
      t.integer :flags
      t.timestamps
    end
  end
end
