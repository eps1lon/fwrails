class Stock < ActiveRecord::Base
  has_many :diffs, :class_name => 'StockChanges'
end
