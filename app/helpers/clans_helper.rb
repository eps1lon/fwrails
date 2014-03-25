module ClansHelper
  def clan_path(clan)
    super(:id => clan.clan_id, :world => clan.world.short)
  end
  
  def tag_with_options(clan)
    class_names = ['clan_tag']
    titles = []
    
    tag = clan.tag_printable

    if clan.tag_is?(:notag)
      class_names << 'notag'
      titles << I18n.t('clans.commons.id_as_tag')
      tag = tag.to_s
    else
      class_names << 'string'

      if clan.tag_is?(:inspected)
        class_names << 'inspected'
        titles << I18n.t('clans.commons.tag_inspected')
      end
    end

    {
      :class_names => class_names,
      :text => tag,
      :titles => titles
    }
  end
  
  def tag_markup(clan, html_tag = 'em')
    options = tag_with_options(clan)
    html_options = {
      :class => options[:class_names].join(" "),
      :title => options[:titles].join("\n")
    }
    
    "<#{html_tag} class='#{html_options[:class]}' title='#{html_options[:title]}'>#{options[:text]}</#{html_tag}>".html_safe
  end
  
  def member_summary(member, clan)
    link_to(member.name, user_url(member.name, clan.world.short)) +
    " (" + number_with_delimiter(member.experience) + " XP) - " +
    link_to(t('users.profile'), member.profile_url, :target => :blank)
  end
end
