class CreateTags < ActiveRecord::Migration
  def change
    create_table :tags, :options => "ENGINE=MyISAM" do |t|
      t.string :name
      t.boolean :reported
      t.timestamps
    end
  end
end
