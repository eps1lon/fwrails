class AddStatusToExtension < ActiveRecord::Migration
  def change
    change_table(:extensions) do |t|
      t.integer :status, null: false, default: 0
    end
  end
end
