class ChangeGfxInAchievements < ActiveRecord::Migration
  def up
    rename_column :achievements, :std_gfx, :gfx
    change_column :achievements, :gfx, :string, :null => false, :default => ''
  end
  
  def down
    rename_column :achievements, :gfx, :std_gfx
    change_column :achievements, :std_gfx, :bool, :null => false, :default => false
  end
end
