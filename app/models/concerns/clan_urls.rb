module ClanUrls
  extend ActiveSupport::Concern
  
  def profile_url
    "#{world.url}internal/fight.php?action=watchclan&act_clan_id=#{clan_id}"
  end
end
