class ItemsPlace < ActiveRecord::Base
  has_many :places, foreign_key: [:pos_x, :pos_y],
                    primary_key: [:pos_x, :pos_y]
  has_many :items
end
