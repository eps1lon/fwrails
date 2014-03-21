class World < ActiveRecord::Base
  belongs_to :language
  
  has_many :clans
  has_many :clans_coleader_changes
  has_many :clans_leader_changes
  has_many :clans_name_changes
  has_many :clans_tag_changes
  has_many :clans_news
  has_many :clans_deletes
  
  has_many :users
  has_many :users_clan_changes
  has_many :users_experience_changes
  has_many :users_name_changes
  has_many :users_race_changes
  has_many :users_news
  has_many :users_deletes
  has_many :users_achievements_caches
  
  has_many :stock_changes
  has_many :statistic_changes
  
  def url
    "http://#{self.subdomain}.freewar.#{self.language.tld}/freewar/"
  end
  
  def urls 
    {
      :chat => "#{self.url}/internal/chattext.php",
      :clans => "#{self.url}/internal/list_clans.php",
      :landing_page => "#{self.url}index.php",
      :login => "#{self.url}",
      :items => "#{self.url}/internal/list_items588.php",
      :npcs => "#{self.url}/internal/list_npcs588.php",
      :players => "#{self.url}/internal/list_players.php",
      :pwmailer => "#{self.url}/pwmailer.php",
      :registration => "#{self.url}register.php",
      :stats => "#{self.url}/internal/list_stats.php",
      :stocks => "#{self.url}/internal/list_stocks.php",
    }
  end
  
  def localized_name
    "#{self.name} #{self.language.locale}"
  end
end
