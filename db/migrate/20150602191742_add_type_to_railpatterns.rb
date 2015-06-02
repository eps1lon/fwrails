class AddTypeToRailpatterns < ActiveRecord::Migration
  def change
    rename_column :railpatterns, :klass, :type
  end
end
