class CreateAdminNews < ActiveRecord::Migration
  def change
    create_table :admin_news do |t|

      t.timestamps
    end
  end
end
