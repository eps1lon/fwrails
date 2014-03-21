class StockChange < ActiveRecord::Base
  belongs_to :stock
  belongs_to :world
end
