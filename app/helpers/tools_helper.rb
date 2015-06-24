module ToolsHelper
  def countdown(t) 
    units = countdown_units(t)
    
    [
      t("datetime.distance_in_words.x_days",    count: units[:d]),
      t("datetime.distance_in_words.x_hours",   count: units[:h]),
      t("datetime.distance_in_words.x_minutes", count: units[:m]),
      t("datetime.distance_in_words.x_seconds", count: units[:s])
    ].reject(&:blank?).to_sentence(last_word_connector: t("support.array.connector.and"), 
                                    two_words_connector: t("support.array.connector.and"))
  end
  
  private
  def countdown_units(t)
    mm, ss = t.divmod(60)            
    hh, mm = mm.divmod(60)          
    dd, hh = hh.divmod(24)          
    {
      d: dd, 
      h: hh, 
      m: mm, 
      s: ss
    }
  end
end
