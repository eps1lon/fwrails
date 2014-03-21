desc "fixes migration"
task :fix_migrate => :environment do
  versions = []
  Dir.open(File.join(Rails.root, 'db', 'migrate')).entries.each do |file|
    versions << file.match(/^(\d+)/)
  end
  
  query = "INSERT INTO schema_migrations VALUES \
           (#{versions.compact.map {|v| v[0] }.sort.join('),(')})"
  puts query
  ActiveRecord::Base.connection.execute(query)
end