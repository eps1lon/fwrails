module GraphsHelper
  def graph_img_url(options = [])
    # map compact!
    options_clean = options.map{ |o| o.delete_if{ |_,v| v.blank? } }
    # merge array of hashes and create query_string
    query = options_clean.reduce({}, :merge).to_query
    
    "/graphs/#{action_name}.php?#{query}"
  end
end
