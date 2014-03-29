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

# Stable path
set :stable_path, deploy_path.join("stable")

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
set :linked_dirs, fetch(:linked_dirs) + %w{public/dumps public/assets}

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
set :keep_releases, 5

namespace :deploy do 
  namespace :symlink do
    desc 'Sets Stable'
    task :stable do
      on roles(:app) do
        latest = capture(:readlink, '', deploy_path.join("current"))
        execute "ln -s '#{latest}' '#{fetch(:stable_path)}'"   
      end
    end
  end

  namespace :passenger do
    desc 'Sets the correct RailsEnv value for Phusion Passenger'
    task :set_environment, :in, :to do |task, args|
      on roles(:app) do
        execute "sed -i 's/RailsEnv .*/RailsEnv #{args[:to]}/' #{deploy_path.join(args[:in], "public", ".htaccess")}" 
      end
      
      Rake::Task["deploy:restart"].invoke(args[:in])
      #invoke "deploy:restart[#{args[:in]}]"
    end
  end
  
  desc 'Restart application'
  task :restart, :in do |task, args|
    on roles(:app), in: :sequence do
      execute :touch, File.join(deploy_to, args[:in], "tmp", "restart.txt")
    end
  end

  after :publishing, :set_environment do
    puts "`#{fetch(:rails_env)}`, `#{fetch(:current_dir)}`, `#{rails_env}`, `#{current_dir}`"
    Rake::Task["deploy:passenger:set_environment"].invoke(fetch(:current_dir), fetch(:rails_env))
  end
  
  after :migrate, :rake do
    on roles(:app) do
      ["assets:precompile"].each do |task|
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
      'lib/cron/mydump.sh' => 700,
      'lib/cron/*.php' => 644
    }.each do |file, permissions|
      execute "chmod #{permissions} #{File.join(current_path, file)}"
    end
  end
end

#maintenance
after :deploy, :reminder do 
  # notification for enabling website
  puts "when everything works fine run `cap production deploy:symlink:stable`"
end