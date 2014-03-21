class CreateLanguages < ActiveRecord::Migration
  def change
    create_table :languages, :options => "ENGINE=MyISAM" do |t|
      t.string :country_code, :language_code, :tld
      t.datetime :created_at
    end
  end
end
