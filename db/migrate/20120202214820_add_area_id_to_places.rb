class AddAreaIdToPlaces < ActiveRecord::Migration
  def change
    add_column :places, :area_id, :integer
    add_index :places, :area_id
  end
end
