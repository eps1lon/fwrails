class AddDetailsToPlaces < ActiveRecord::Migration
  def change
    add_column :places, :name,  :string
    add_column :places, :desc,  :string
    add_column :places, :gfx,   :string
    add_column :places, :pos_x, :integer
    add_column :places, :pos_y, :integer
    add_column :places, :flags, :integer
  end
end
