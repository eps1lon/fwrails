module ClansHelper
  def clan_path(clan)
    super(:id => clan.clan_id, :world => clan.world.short)
  end
  
  module Tag 
    # tag flags
    FLAGS = []
    
    def Tag.as_em(clan)
      class_names = ['clan_tag']
      titles = []
      
      printable = clan.tag_with_flags
      tag = printable[:tag]

      if printable[:flags] & FLAGS[:numeric] # tag is equal to clan_id
        class_names << 'numeric'
        titles << I18n.t('clans.commons.id_as_tag')
        tag = tag.to_s
      else
        class_names << 'string'
        
        if printable[:flags] & FLAGS[:inspected]
          class_names << 'inspected'
          titles << I18n.t('clans.commons.tag_inspected')
        end
      end
      
      '<em class="' + class_names.join(" ") + '" title="' + titles.join("\n")  + '">' + tag + '</em>'
    end
  end
  
  def member_summary(member, clan)
    link_to(member.name, user_url(member.name, clan.world.short)) +
    " (" + number_with_delimiter(member.experience) + " XP) - " +
    link_to(t('users.profile'), member.profile_url, :target => :blank)
  end
end
