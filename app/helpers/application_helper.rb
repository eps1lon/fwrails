module ApplicationHelper
  def root_url_without_trailing_slash
    request.protocol + request.host_with_port
  end
  
  def contact_mail
    "contact@fwrails.net"
  end
  
  def profile_url(user_relation)
    user_relation.profile_url
  end
  
  def achievement_profile_url(user_relation, achievement = nil)
    user_relation.profile_achievement_url(achievement)
  end
  
  def freewar_url(lang = :de) 
    tlds = {
      en: 'com'
    }
    tld = tlds[lang] || 'de'
    "http://freewar.#{tld}"
  end
  
  def wiki_url(page, lang = :de)
    tlds = {
      en: 'com'
    }
    tld = tlds[lang] || 'de'
    "http://www.fwwiki.#{tld}/index.php/#{page.gsub(/\s?(-|\/)\s?/, '\1')}"
  end
  
  def link_to_wiki(content, page = nil)
    page = content if page.nil?
    link_to content, wiki_url(page), :class => 'wiki', :target => :blank
  end
  
  def link_to_user_or_del(user, user_id, world_short, name_method = :name_primary)
    if user.nil?
      content_tag :del, class: %w{user tooltip} do
        ''.html_safe + "{#{user_id}}" + tooltip_markup(t("users.common.deleted"))
      end
    else
      link_to user.send(name_method), user_url(user.name, world_short)
    end
  end
  
  def link_to_clan_or_del(clan, tag, world_short)
    if clan.nil?
      content_tag :del, class: %w{clan tooltip} do
        ''.html_safe + "{#{tag}}" + tooltip_markup(t("clans.commons.deleted"))
      end
    else
      link_to(tag_markup(clan, 'em'), clan_url(clan.clan_id, world_short))
    end     
  end
  
  # creates markup for loading.css
  def loading_markup
    "<ul class='spinner'><li/><li/><li/></ul>".html_safe
  end
  
  # creates markup for tooltip.css
  def tooltip_markup(markup)
    content_tag :span, markup.html_safe
  end
  
  def render_locale_partial(partial = nil, locale = nil)
    partial ||= action_name
    locale ||= I18n.locale
        
    begin
      render :partial => "#{locale}.#{partial}"
    rescue ActionView::MissingTemplate
      t "errors.messages.locale_not_available"
    end
  end
  
  # checks if translations exists
  # setting default may not be viable since it will always return 
  # a string and not the original var type
  def i18n_set? key
    I18n.t key, :raise => true rescue false
  end
  
  ##
  # alternitive could be link_to "back", :back
  # but this uses javascript and has no fallback
  # ugly if this page is an entry page
  ##
  def back_url
    ref = request.referer
    pattern = /^#{root_url}.*/i
    unless pattern.match(ref).nil?
      url = ref
    else
      url = "#{root_url.chop}#{url_for :controller => params[:controller], :action => 'index'}"
    end
    
    url
  end
  
  # creates a number image path for soul capsule
  def capsule_map_path(count, world)
    "#{world.urls[:images]}/map/npczahl#{count}.gif"
  end
  
  def link_to_back
    link_to t("helpers.nav.back"), back_url, :class => "back"
  end
  
  def relation_to_cache_key(relation)
    relation.to_a.map { |base| base.cache_key  }
  end
  
  ## Nav
  def nav(offset, limit, count, url_helper)
    links = []
  
    last_page = page(count, limit)
    cur_page = page(offset, limit)
    
    pages = [cur_page - 2, 1].max..[cur_page + 2, last_page].min
    
    unless pages.include?(1)
      links << link_to_nav(1, url_helper, :class => 'forced')
    end
   
    pages.each do |page|
      links << link_to_nav(page, url_helper)
    end
    
    unless pages.include?(last_page)
      links << link_to_nav(last_page, url_helper, :class => 'forced')
    end

    links
  end
  
  def position_text(x, y)
    "X: #{number_with_delimiter(x)} Y: #{number_with_delimiter(y)}"
  end
  
  private
  def link_to_nav(page, url_helper, options = {})
    options[:class] = ([options[:class]] << ('active' if page == @params[:page])).compact
    link_to page, 
            url_helper.call(@params.merge({"page" => page})), 
            :class => ["page"] + options[:class], 
            :data => {:page => page}
  end
  
  def next_link(page, add, upper, url_helper)
    page += add
    link_to_nav(page, url_helper) if page > 0 && page < upper
  end

  def page(offset, limit)
    (offset / limit).floor + 1
  end
end