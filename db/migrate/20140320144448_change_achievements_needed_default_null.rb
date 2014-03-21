class ChangeAchievementsNeededDefaultNull < ActiveRecord::Migration
  def up
    change_column :achievements, :needed, :integer, :default => nil
  end
  
  def down
    change_column :achievements, :needed, :integer, :default => 1
  end
end
