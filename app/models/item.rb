class Item < ActiveRecord::Base
  has_many :places, through: :positions
  has_many :positions, class_name: 'ItemsPlace'
end
