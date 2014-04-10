class FeedsController < ApplicationController
  def news
    # this will be the name of the feed displayed on the feed reader
    @title = I18n.translate("feeds.news.title")

    # the news items
    @news = News.for_feed

    # this will be our Feed's update timestamp
    @updated = @news.take.try(:updated_at)

    respond_to do |format|
      format.atom { render layout: false }

      # we want the RSS feed to redirect permanently to the ATOM feed
      format.rss { redirect_to news_feed_path(format: :atom), status: :moved_permanently }
    end
  end
end
