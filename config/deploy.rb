# config valid only for Capistrano 3.1
lock '3.1.0'

set :application, 'freewar3'

# RVM bootstrap

set :rvm_type, :system  # Copy the exact line. I really mean :system here
set :rvm_ruby_string, ENV['GEM_HOME'].gsub(/.*\//,"")  # Read from local system
#set :bundle_cmd, '/path/to/project/rvm/bundle'
set :default_shell, :bash

# Repo Setup
set :scm, :git
set :user, 'capistrano'
set :repo_url,  "#{fetch(:user)}@fwrails.net:freewar3.git"

# server details


set :ssh_options, {
  keys: %w(/home/capistrano/.ssh/id_rsa),
  forward_agent: true,
  user: fetch(:user)
}

set :deploy_to, "/var/www/vhosts/fwrails.net/rails"
set :deploy_via, :checkout

# Default branch is :master
# ask :branch, proc { `git rev-parse --abbrev-ref HEAD`.chomp }

# Default value for :format is :pretty
set :format, :pretty

# Default value for :log_level is :debug
set :log_level, :debug

# Default value for :pty is false
set :pty, true

# Default value for :linked_files is []
set :linked_files, %w{config/database.yml}

# Default value for linked_dirs is []
set :linked_dirs, %w{bin log tmp/pids tmp/cache tmp/sockets vendor/bundle}
set :linked_dirs, fetch(:linked_dirs) + %w{public/dumps}

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
set :keep_releases, 5

namespace :deploy do 
  desc 'Restart application'
  task :restart do
    on roles(:app), in: :sequence, wait: 5 do
      execute :touch, release_path.join('tmp/restart.txt')
    end
  end

  after :publishing, :restart
  
  after :migrate, :rake do
    on roles(:app) do
      ["load:achievements", "jslang", "translate:models"].each do |task|
        execute "cd #{release_path} && (RAILS_ENV=#{fetch(:rails_env)} "+
                "#{fetch(:rvm_path)}/bin/rvm default do bundle exec rake #{task})"
      end
    end
  end
      
  after :restart, :clear_cache do
    on roles(:web), in: :groups, limit: 3, wait: 10 do
      # Here we can do anything such as:
      # within release_path do
      #   execute :rake, 'cache:clear'
      # end
    end
  end
end

# Fix Permissions
after :deploy, :fix_permissions do
  on roles(:app) do
    {
      'lib/tasks/mydump.sh' => 700,
      'lib/tasks/*.php' => 644
    }.each do |file, permissions|
      execute "chmod #{permissions} #{File.join(current_path, file)}"
    end
  end
end

#maintenance
before :deploy, "deploy:web:disable"
after :deploy, :reminder do 
  # notification for enabling website
  puts "dont forget to precompile assets then `cap deploy:web:enable`"
end