module HomeHelper
  def news_format(text)
    simple_format(text, {class: %w{news content}}, {wrapper_tag: "p", sanitize: false})
  end
end
