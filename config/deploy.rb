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

# current path
set :current_dir, "current"

# Default branch is :master
# ask :branch, proc { `git rev-parse --abbrev-ref HEAD`.chomp }

# Default value for :format is :pretty
set :format, :pretty

# Default value for :log_level is :debug
set :log_level, :debug

# Default value for :pty is false
set :pty, true

# Default value for :linked_files is []
set :linked_files, %w{config/database.yml config/initializers/secret_token.rb}

# Default value for linked_dirs is []
set :linked_dirs, %w{bin log tmp/pids tmp/cache tmp/sockets vendor/bundle}
set :linked_dirs, fetch(:linked_dirs) + %w{public/dumps}

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
set :keep_releases, 5

namespace :deploy do 
  namespace :assets do 
    desc 'pushes assets'
    task :sync do
      on roles(:all) do
        public_dir = "public"
        asset_dir = File.join(public_dir, "assets")
        
        releases = capture(:ls, '-X', releases_path).split.sort
        previous_release_path = releases_path.join(releases[-2])
        release_path = releases_path.join(releases[-1])
        
        # sync with old, skip if no assets exists
        execute "cp -R #{previous_release_path.join(asset_dir)} #{release_path.join(public_dir)} || :"
        
        #servers = find_servers_for_task(current_task)
        servers = %w{fwrails.net}
        servers.each do |server|
          run_locally do
            execute :rsync, '-av --size-only', File.join(asset_dir, ""), "#{fetch(:user)}@#{server}:#{release_path.join(asset_dir)}"
          end
        end
      end
    end
  end

  namespace :passenger do
    desc 'Sets the correct RailsEnv value for Phusion Passenger'
    task :set_environment do 
      on roles(:app) do
        execute "sed -i 's/RailsEnv .*/RailsEnv #{fetch(:stage)}/' #{deploy_path.join(fetch(:current_dir), "public", ".htaccess")}" 
      end

      invoke "deploy:restart"
    end
  end
  
  desc 'Restart application'
  task :restart do 
    on roles(:app), in: :sequence do
      execute :touch, deploy_path.join(fetch(:current_dir), "tmp", "restart.txt")
    end
  end
  
  desc 'Uploads Files specified in FILES'
  task :upload do
    on roles(:all) do
      puts "upload #{ENV['FILES']} to #{release_path}"
      upload!(ENV['FILES'], release_path) unless ENV['FILES'].nil?
    end
  end
  
  after :migrate, :rake do
    on roles(:app) do
      ["tmp:cache:clear"].each do |task|
        execute "cd #{release_path} && (RAILS_ENV=#{fetch(:rails_env)} "+
                "#{fetch(:rvm_path)}/bin/rvm default do bundle exec rake #{task})"
      end
    end
  end

  after :updating, "deploy:assets:sync"
end

# Fix Permissions
after :deploy, :fix_permissions do
  on roles(:app) do
    {
      'lib/cron/mydump.sh' => 700,
      'lib/cron/*.php' => 644
    }.each do |file, permissions|
      execute "chmod #{permissions} #{File.join(current_path, file)}"
    end
  end
end

#maintenance
before :deploy, "deploy:passenger:set_environment"

after :deploy, :reminder do 
  # notification for enabling website
  puts "when everything works fine run `cap production deploy:passenger:set_environment`"
end