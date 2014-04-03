module UserUrls
  extend ActiveSupport::Concern
    
  # URL to the users's ingame profil
  def profile_url
    "#{world.url}internal/fight.php?action=watchuser&act_user_id=#{user_id}"
  end
  
  def rank_url
    "#{world.url}internal/fight.php?action=userrang&act_user_id=#{user_id}"
  end
  
  # link to achievement profile including anchor
  def achievement_url(achievement)
    profile_achievement_url(achievement)
  end
  
  # link to achievement profile
  def profile_achievement_url(achievement = nil)
    "#{world.url}internal/achiev.php?act_user_id=#{user_id}##{achievement.try(:link_anchor)}"
  end
end
