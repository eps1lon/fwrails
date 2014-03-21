class AddShortToRaces < ActiveRecord::Migration
  def change
    add_column :races, :short, :string
  end
end
