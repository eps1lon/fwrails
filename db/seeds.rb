# -*- coding: utf-8 -*-
## Languages
[
  {
    :id => 1,
    :country_code => 'de',
    :language_code => 'de',
    :tld => 'de'
  },
  {
    :id => 2,
    :country_code => 'en',
    :language_code => 'en',
    :tld => 'com'
  }
].each do |language|
  Language.create(language)
end
## Languages end

## Worlds
# de-DE
# number 
1.upto(14) do |i|
  World.create({      
      :id => i,
      :name => "Welt #{i}",
      :short => "W#{i}",
      :subdomain => "welt#{i}",
      :language_id => 1
    })
end
# AF/RP
World.create({   
    :name => "Action Freewar",
    :short => "AF",
    :subdomain => "afsrv",
    :language_id => 1
  })
World.create({
    :name => "RP Freewar",
    :short => "RP",
    :subdomain => "rpsrv",
    :language_id => 1
  })

# en-EN
# number 
1.upto(1) do |i|
  World.create({
      :name => "World #{i}",
      :short => "World #{i}",
      :subdomain => "world#{i}",
      :language_id => 2
    })
end
## Worlds end

## Races
# order given by external data: http://freewar.behindthematrix.de/viewtopic.php?f=2&t=13
[
  {
    :id => 1,
    :name => "Taruner",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 1
  },
  {
    :id => 2,
    :name => "dunkler Magier",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 1
  },
  {
    :id => 3,
    :name => "Natla - Händler",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 0
  },
  {
    :id => 4,
    :name => "Mensch / Kämpfer",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 0
  },
  {
    :id => 5,
    :name => "Mensch / Zauberer",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 1
  },
  {
    :id => 6,
    :name => "Mensch / Arbeiter",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 1
  },
  {
    :id => 7,
    :name => "Onlo",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 1
  },
  {
    :id => 8,
    :name => "Serum-Geist",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 1
  },
  {
    :id => 9,
    :name => "Quest Person",
    :base_live => 1,
    :base_strength => 1,
    :base_intelligence => 1,
    :flags => 0
  }
].each do |race|
  Race.create(race)
end

[
  {
    :id => 1,
    :name => "Teidam Burger Corporation"
  },
  {
    :id => 2,
    :name => "Herberge Vulkanblick Ag"
  },
  {
    :id => 3,
    :name => "Bank aller Wesen"
  },
  {
    :id => 4,
    :name => "Sandstaub Corporation"
  },
  {
    :id => 5,
    :name => "Casino des Nordens Ag"
  },
  {
    :id => 6,
    :name => "Post Corporation"
  }
].each do |stock|
  Stock.create(stock)
end

[
  {
    :id => 1,
    :name => 'money_bank',
  },
  {
    :id => 2,
    :name => 'money_items'
  },
  {
    :id => 3,
    :name => 'money_clans'
  },
  {
    :id => 4,
    :name => 'money_boerse'
  },
  {
    :id => 5,
    :name => 'money_users'
  },
  {
    :id => 6,
    :name => 'money_gesamt'
  },
  {
    :id => 7,
    :name => 'no_items'
  },
  {
    :id => 8,
    :name => 'no_user'
  },
  {
    :id => 9,
    :name => 'no_user_in_clans'
  },
  {
    :id => 10,
    :name => 'no_user_active'
  },
  {
    :id => 11,
    :name => 'no_sex_M'
  },
  {
    :id => 12,
    :name => 'no_sex_N'
  },
  {
    :id => 13,
    :name => 'no_sex_W'
  }
].each do |statistic|
  Statistic.create(statistic)
end
## Races end