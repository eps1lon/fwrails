class SolveNullComparision < ActiveRecord::Migration
  TABLES = {
    :users => [:race_id, :clan_id, :name, :experience],
    :clans => [:tag, :name, :leader_id, :coleader_id]
  }
  TABLES[:clans_old] = TABLES[:clans]
  TABLES[:users_old] = TABLES[:users]
  
  def up
    defaults = {
      'integer' => 0,
      'string' => ''
    }
    
    TABLES.each do |(table_name, columns)|
      columns.each do |column_name|
        default = defaults[columns(table_name).detect {|col| col.name == column_name.to_s}.type.to_s]
        change_column_null table_name, column_name, false, default
      end
    end
  end
  
  def down
    TABLES.each do |(table_name, columns)|
      columns.each do |column_name|
        change_column_null table_name, column_name, true, nil
      end
    end
  end
end
