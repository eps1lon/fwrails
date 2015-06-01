source 'http://rubygems.org'

gem 'rails', "~> 4.1.4"
gem 'passenger', "~> 4.0.37 "

# Bundle edge Rails instead:
# gem 'rails',     :git => 'git://github.com/rails/rails.git'

gem 'mysql2', "~> 0.3.15"
gem "composite_primary_keys", "=7.0.13"
# http://stackoverflow.com/questions/11023167/no-such-file-to-load-active-record-associations-has-and-belongs-to-many-associat/23142066#23142066
#gem 'composite_primary_keys', {
#  :git => 'git://github.com/composite-primary-keys/composite_primary_keys.git',
#  :branch => 'ar_4.1.x'
#}

# Gems used only for assets and not required
# in production environments by default.
group :assets do
  # http://stackoverflow.com/questions/9779456/rake-aborted-stack-level-too-deep-while-deploying-to-heroku#9828925
  gem 'sass-rails', "~> 4.0.1"
  #
  gem 'coffee-rails', "~> 4.1.0"
  gem 'uglifier', "~> 2.4.0"
  gem "yui-compressor", "~> 0.12.0"
end

# js libs
gem 'jquery-rails', "~> 3.1.0"
gem 'jquery-ui-rails', "~> 4.1.1"
gem "i18n-js", "~> 2.1.2"

# Deploy with Capistrano
gem 'capistrano', '=3.1', require: false, group: :development

group :development do
  gem 'capistrano-rails',   '~> 1.1', require: false
  gem 'capistrano-bundler', '~> 1.1', require: false
  gem 'capistrano-rvm',   '~> 0.1', require: false
end

# To use debugger
# gem 'ruby-debug19', :require => 'ruby-debug'

group :test do
  # Pretty printed test output
  gem 'turn', "~> 0.9.6", :require => false
end

# fixes usr/local/lib/ruby/gems/1.9.1/gems/execjs-1.2.9/lib/execjs/runtimes.rb:47:in `autodetect': Could not find a JavaScript runtime
gem 'execjs', "~> 2.0.2"
gem 'therubyracer', "~> 0.12.1"

# rake
gem "rake", "~> 10.1.1", :require => false

# auth logic
gem "devise", "~>3.2.4"

# for has_secure_password
#gem 'bcrypt', '~> 3.1.7'

gem "request-log-analyzer"