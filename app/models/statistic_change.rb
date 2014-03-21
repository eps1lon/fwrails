class StatisticChange < ActiveRecord::Base
  belongs_to :statistic
  belongs_to :world
end
