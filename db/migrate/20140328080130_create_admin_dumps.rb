class CreateAdminDumps < ActiveRecord::Migration
  def change
    create_table :admin_dumps do |t|

      t.timestamps
    end
  end
end
