ANALYTICS = HashWithIndifferentAccess.new

config = YAML.load_file(Rails.root.join("config", "analytics.yml"))[Rails.env]
if config
  ANALYTICS.update(config)
end