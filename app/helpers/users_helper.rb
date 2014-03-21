module UsersHelper 
  def cache_key_for_users_index(users)
    last_update = User.last_update.try(:utc).try(:to_s, :number)
    user_cache_key = relation_to_cache_key(users)
    ["users", "index", last_update] + user_cache_key
  end
  
  def clan_status(user)
    if user.clan.nil?
      t ".noclan"
    else 
      link_to(user.clan.tag, clan_url(user.clan.clan_id, user.world.short))
    end
  end
end
