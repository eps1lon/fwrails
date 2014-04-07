class AddIndexToUsersClanChanges < ActiveRecord::Migration
  def change
    change_table :users_clan_changes do |t|
      t.index :clan_id_old
      t.index :clan_id_new
    end
  end
end
