class CreateNpcs < ActiveRecord::Migration
  def change
    create_table :npcs, :options => "ENGINE=MyISAM" do |t|

      t.timestamps
    end
  end
end
