class News < ActiveRecord::Base
  before_save :set_defaults
  
  attr_accessor :max_content_summary_count,
                :content_summary_sep
  
  def content_summary
    self.content.split(self.content_summary_sep)[0..self.max_content_summary_count-1]
                 .join(self.content_summary_sep) + 
    ("#{self.content_summary_sep}…" if self.content_summarized?)
  end
  
  def max_content_summary_count
    @max_content_summary_count || 20
  end
  
  def content_summary_sep
    @content_summary_sep || " "
  end
  
  def public_updated_at
    [self.publish_at, self.updated_at].max
  end
  
  def published?
    self.publish_at > Time.now
  end
  
  def content_summarized?
    self.content.split(self.content_summary_sep).length > self.max_content_summary_count
  end
  
  def to_param
    [self.id, self.heading.parameterize].join("-")
  end
  
  def self.published
    where("publish_at >= ?", DateTime.now)
  end
  
  private 
  # defaults that are not supported by rails migrations
  def set_defaults
    self.publish_at ||= DateTime.now if new_record?
  end
end
