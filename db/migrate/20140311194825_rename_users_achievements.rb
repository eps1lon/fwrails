class RenameUsersAchievements < ActiveRecord::Migration
  def change
    drop_table :users_achievements
    rename_table :users_achievements_progresses, :users_achievements
    rename_table :users_achievements_progresses_old, :users_achievements_old
    rename_table :users_achievements_progress_changes, :users_achievements_changes
  end
end
