class ChangeAchievements < ActiveRecord::Migration
  def up
    change_table :achievements do |t|
      t.remove :gfx_file
      t.boolean :std_gfx, :null => false, :default => false
    end
  end
  
  def down
    change_table :achievements do |t|
      t.remove :std_gfx
      t.string :gfx_file
    end
  end
end
