module GraphsHelper
  def graph_img_path(options = [])
    "/graphs/#{action_name}.php?base64=#{graph_options_for_query(options)}"
  end
  
  def graph_img_url(options = [])
    root_url_without_trailing_slash + graph_img_path(options)
  end
  
  def graph_profile_code(graph_options = [])
    # skip url escape since freewar escapes url escapes
    config_url = URI.decode(url_for(:only_path => false, :base64 => graph_options_for_query(graph_options)))
    graph_url = graph_img_url(graph_options)
    "[url=#{config_url}][img]#{graph_url}[/img][/url]"
  end
  
  def graph_options_for_query(options = [])
    # merge array of hashes and create query_string
    # we need to encode the query string since freewar escapes url escapes
    Base64.encode64(options.reduce({}, :merge).compact.to_query)
  end
end
