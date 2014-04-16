class AddTldToWorlds < ActiveRecord::Migration
  def up
    add_column :worlds, :tld, :string
    execute "UPDATE worlds, languages SET worlds.tld = languages.tld WHERE worlds.language_id = languages.id"
  end
  
  def down
    remove_column :worlds, :tld
  end
end
