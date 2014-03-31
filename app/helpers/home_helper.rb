module HomeHelper
  def news_format(text)
    #simple_format(text, {class: %w{news content}}, {wrapper_tag: "p", sanitize: false})
    render inline: text
  end
  
  def news_action_bar(news, options = {})
    options[:glue] ||= " - "
    options[:wrap] ||= %w{[ ]}
    
    action_bar = {
      show: link_to('Show', news),
      edit: link_to('Edit', edit_admin_news_path(news)),
      destroy: link_to('Destroy', [:admin, news], 
                       method: :delete, data: { confirm: 'Are you sure?' })
    }
    options[:only] ||= action_bar.keys
    
    # we want to be able to wrap around text nodes and content tags
    action_bar.except(options[:except]).only(options[:only]).values
              .map { |a| options[:wrap][0] + a.html_safe + options[:wrap][1] }
              .join(options[:glue]).html_safe
    #safe_join([options[:wrap][0], 
    #           safe_join(action_bar, options[:wrap][1] + options[:glue] + options[:wrap][0]), 
    #           options[:wrap][1]])
  end
end
