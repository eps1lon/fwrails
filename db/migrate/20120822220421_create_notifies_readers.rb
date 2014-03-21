class CreateNotifiesReaders < ActiveRecord::Migration
  def change
    create_table :notifies_readers, :id => false do |t|
      t.integer :notify_id
      t.integer :reader_id
      t.integer :world_id
    end
  end
end
