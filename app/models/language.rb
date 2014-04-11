class Language < ActiveRecord::Base
  has_many :worlds
  
  def self.find_by_language_code(language_code = nil)
    language_code ||= I18n.default_locale
    language = where(language_code: language_code).take
    language = where(language_code: I18n.default_locale).take if language.nil?
    language
  end
  
  def locale
    "#{self.language_code.downcase}_#{self.country_code.upcase}"
  end
end
