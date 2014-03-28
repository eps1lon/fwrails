module DumpsHelper
  def dump_path(dump)
    "/dumps/#{dump.web_path}"
  end
end
