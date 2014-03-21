class Category < ActiveRecord::Base
  has_many :images
  
  def self.areas
    self.where(:category_type => 1)
  end
  
  def self.sets
    self.where(:category_type => 2)
  end
end
