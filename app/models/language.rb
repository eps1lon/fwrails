class Language < ActiveRecord::Base
  has_many :worlds
  
  def locale
    "#{self.language_code.downcase}_#{self.country_code.upcase}"
  end
end
