module StatisticsHelper
  def statistic_url(statistic)
    super(statistic.name)
  end
  
  def statistic_path(statistic)
    super(statistic.name)
  end
  
  def cache_key_for_statistics(worlds)
    last_update = Statistic.last_update.try(:utc).try(:to_s, :number)
    world_cache_key = relation_to_cache_key(worlds)
    ["statistics", last_update] + world_cache_key
  end
end
