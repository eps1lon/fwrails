desc "creates de.yml from database (de is default language for text columns)"
namespace "translate" do
  task :records => :environment do 
    yaml = {}
    
    models = {
      Achievement => ["description"]
    }
    
    models.each do |model, attributes|
      yaml[model.name.downcase] = {}
      model.find_each do |row|
        yaml[model.name.downcase][row.id.to_s] = {}
        attributes.each do |attr|
          yaml[model.name.downcase][row.id.to_s][attr] = row[attr]
        end
      end
    end
    
    Achievement.base_stage.find_each do |row|
      yaml['achievement'][row.achievement_id.to_s.to_sym] = {
        'name' => {
          'one' => row.group_name,
          'other' => row.group_name
        }
      }
    end
    
    File.open(File.join(Rails.root, 'config', 'locales', 'activerecord', 'de.yml'), "w") do |f|
      f.write({'de' => {'activerecord' => yaml}}.to_yaml)
      f.close  
    end
  end
end