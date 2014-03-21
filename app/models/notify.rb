class Notify < ActiveRecord::Base
  has_and_belongs_to_many :readers
end
