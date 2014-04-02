module ActiveRecordRandom
  extend ActiveSupport::Concern
  
  included do
    # similar to array.sample
    scope :sample, ->(options = {}) { 
      options[:limit] ||= 1
      options[:rng] ||= Random.new
      offset(options[:rng].rand(self.count)).limit(options[:limit]) 
    }
  end
end
