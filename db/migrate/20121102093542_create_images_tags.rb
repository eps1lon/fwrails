class CreateImagesTags < ActiveRecord::Migration
  def up
    create_table :images_tags, :id => false, :options => "ENGINE=MyISAM" do |t|
      t.integer :image_id, :tag_id
      t.integer :votes_down, :votes_up
      t.timestamps
    end
    
    execute "ALTER TABLE images_tags ADD PRIMARY KEY(image_id, tag_id)"
  end
  
  def down
    drop_table :images_tags
  end
end
