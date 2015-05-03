module Freewar3
  class Application < Rails::Application
    # precompile all
    config.assets.precompile += ['*']
  end
end