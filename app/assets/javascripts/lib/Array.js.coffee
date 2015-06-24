# Rails Array.to_sentence method
Array.prototype.to_sentence ?= (options) ->
  options ||= {}
  
  # defaults
  options.locale ||= I18n.currentLocale();
  options.words_connector ||= I18n.t("support.array.words_connector", {locale: options.locale})
  options.two_words_connector ||= I18n.t("support.array.two_words_connector", {locale: options.locale})
  options.last_word_connector ||= I18n.t("support.array.last_word_connector", {locale: options.locale})
  
  
  # this.map toString()
  words = (word.toString() for word in @)
  
  switch words.length
    when 0 then ""
    when 1 then words[0]
    # "#{self[0]}#{options[:two_words_connector]}#{self[1]}"
    when 2 then words[0] + options.two_words_connector + words[1]
    # words[0..-1] !=> words.slice(0, -1)
    # words[-1] !=> words.slice(-1)
    else words.slice(0, -1).join(options.words_connector) + options.last_word_connector + words.slice(-1)
  
  