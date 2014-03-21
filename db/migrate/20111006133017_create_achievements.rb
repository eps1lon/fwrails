class CreateAchievements < ActiveRecord::Migration
  def up
    create_table :achievements, :options => "ENGINE=MyISAM", :force => true do |t|
      t.string :name, :gfx_file, :description     
      t.integer :stage, :default => 1
      t.integer :max_stage, :default => 1
      t.integer :reward     
      t.integer :needed, :default => 1
      t.integer :achievement_group
      t.datetime :created_at
    end
  end
  
  def down
    drop_table :achievements
  end
end
