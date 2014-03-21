class AddStageToUsersAchievementsProgresses < ActiveRecord::Migration
  def change
    add_column :users_achievements_progresses, :stage, :integer, :default => 0
    add_column :users_achievements_progresses_old, :stage, :integer, :default => 0
  end
end
