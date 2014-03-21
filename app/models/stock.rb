class Stock < ActiveRecord::Base
  has_many :changes, :class_name => 'StockChanges'
end
