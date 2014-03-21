desc "creates de.yml from database (de is default language for text columns)"
namespace "translate" do
  task :models => :environment do
    models = {
      Achievement => ["name", "description"]
    }
    dir = "#{Rails.root}/config/locales/model/"

    models.each do |model, attributes|
      File.open("#{dir}#{model.name.downcase}.yml", "w+") do |f|
        yaml = {}
        model.find_each do |row|
          yaml["id_#{row.id}"] = {}
          attributes.each do |attr|
            yaml["id_#{row.id}"][attr] = row[attr]
          end
        end

        f.write({"de" => {"activemodel" => {model.name.downcase => yaml}}}.to_yaml)
        f.close
      end    
    end
  end
end