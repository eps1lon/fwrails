module ExtensionsHelper
  def extensions_download_path(extension)
    "extensions/#{extension.filename}"
  end
  
  def translate_status(status)
    t "extensions.status.#{status.to_s}"
  end
end
