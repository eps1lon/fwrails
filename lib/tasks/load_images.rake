desc "loads different images"
namespace "load" do
  desc "loads achievements"
  task :achievements, [:id] => [:environment] do |t, args| 
    require 'net/http'
    require 'fileutils'
   
    path = "#{Rails.root}/app/assets/images/achievements"
    FileUtils.mkdir_p path
    
    groups = Achievement
    
    unless args[:id].nil?
      groups = groups.where(:achievement_id => args[:group])
    end
    
    groups.find_each do |achievement| # get each achievement
      Net::HTTP.start("welt1.freewar.de") { |http|
        filename = achievement.gfx_file
        resp = http.get("/freewar/images/achiev/#{filename}")

        case resp
        when Net::HTTPSuccess
          open("#{path}/#{filename}", "wb") { |file|
            file.write(resp.body)
          }
          puts "successfully loaded #{filename}"
        else
          puts "skipped #{filename}"
        end         
      }
    end
  end
end