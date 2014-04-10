atom_feed language: @language.locale do |feed|
  feed.title @title
  feed.updated @updated

  @news.each do |item|
    feed.entry( item ) do |entry|
      entry.url news_url(item)
      entry.title item.title
      entry.content item.content, type: 'html'

      # the strftime is needed to work with Google Reader.
      entry.updated(item.updated_at.strftime("%Y-%m-%dT%H:%M:%SZ")) 

      entry.author do |author|
        author.name item.author.name
      end
    end
  end
end