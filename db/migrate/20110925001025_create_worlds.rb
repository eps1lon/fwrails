class CreateWorlds < ActiveRecord::Migration
  def change
    create_table :worlds, :options => "ENGINE=MyISAM" do |t|
      t.string :name, :subdomain, :short
      t.integer :language_id
      t.datetime :created_at
    end
  end
end
