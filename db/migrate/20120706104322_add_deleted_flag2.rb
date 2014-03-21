class AddDeletedFlag2 < ActiveRecord::Migration
  def change
    [:users_news, :users_achievements_caches, 
     :users_achievements_progresses, :users_achievements_progress_changes].each do |table|
      add_column table, :deleted, :boolean, :default => 0, :null => false
    end
  end
end
