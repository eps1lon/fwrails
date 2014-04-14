module Timestamps
  extend ActiveSupport::Concern
  
  included do 
    scope :in_recording_period_date, ->(date_period) { 
      # including last date
      date_period = date_period.begin..(date_period.end + 1.day)
      where(created_at: date_period) 
    }
  end
  
  module ClassMethods
    def record_period
      record = select("MAX(created_at) as max, MIN(created_at) as min").take
      record.min..record.max
    end
  end
end
