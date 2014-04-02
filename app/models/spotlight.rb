class Spotlight
  # i18n_key: model.model_name exceeds stack depth
  
  def initialize(refresh_every)
    @refresh_every = refresh_every
  end
 
  def clan
    rng = random
    case rng.rand(2)
    when 0
      model = [ClansNameChange, ClansTagChange, ClansLeaderChange, ClansColeaderChange].sample(random: rng)
      record = model.active.select(Clan.primary_keys, "COUNT(*) AS num")
                           .group(Clan.primary_keys).order("num DESC").take
      hash = {num: record.num, i18n_key: record.class.name, clan: record.clan}
    when 1
      record = User.in_clans.select(Clan.primary_keys, "COUNT(*) AS num")
                   .group(Clan.primary_keys)
                   .order("num DESC").take
      hash = {num: record.num, i18n_key: "Member", clan: record.clan}
    end
    hash[:clan] = Spotlight.spotlight_as_json(hash[:clan], include: :world)
    hash
  end
  
  def user
    rng = random
    case rng.rand(2)
    when 0
      model = [UsersClanChange, UsersNameChange, UsersRaceChange].sample(random: rng)
      record = model.active.select(User.primary_keys, "COUNT(*) AS num").group(User.primary_keys).order("num DESC").take
      hash = {num: record.num, i18n_key: record.class.name, user: record.user}
    when 1
      achievement = Achievement.levelable.base_stage.sample(random: rng).take
      record = UsersAchievements.select(User.primary_keys, :progress)
                                .where(achievement_id: achievement.achievement_id)
                                .group(User.primary_keys)
                                .order("progress DESC").take
      hash = {num: record.progress, i18n_key: "achievement_id_#{achievement.achievement_id}", user: record.user}
    end
    
    
    hash[:user] = Spotlight.spotlight_as_json(hash[:user], include: :world)
    hash
  end
  
  def cache_key
    Digest::MD5.hexdigest random.seed.to_s
  end
  
  # this should be done in as json and the JsonCache concern
  # but if we parse the class into an object we cant have the 
  # JsonCache concern
  # or we have to override Object.to_json
  def to_json(options={})
    Rails.cache.fetch expand_cache_key(self.class.to_s.underscore, cache_key, 'to-json') do
      {
        clan: clan,
        user: user
      }.to_json
    end
  end
  
  private 
  def random
    seed = (Time.now.to_i / @refresh_every).to_i
    Random.new(seed)
  end
  
  def expand_cache_key(*args)
    ActiveSupport::Cache.expand_cache_key args
  end
  
  def self.spotlight_as_json(spotlight, options)
    spotlight.as_json(options.merge(methods: :name_primary))
  end
end
