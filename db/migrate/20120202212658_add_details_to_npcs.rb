class AddDetailsToNpcs < ActiveRecord::Migration
  def change
    add_column :npcs, :name,       :string
    add_column :npcs, :desc,       :string
    add_column :npcs, :gfx,        :string
    add_column :npcs, :pos_x,      :integer
    add_column :npcs, :pos_y,      :integer
    add_column :npcs, :unique_npc, :integer 
    add_column :npcs, :live,       :integer
    add_column :npcs, :strength,   :integer
    add_column :npcs, :maxdmg,     :integer
    add_column :npcs, :flags,      :integer
    add_column :npcs, :killcount,  :integer
  end
end