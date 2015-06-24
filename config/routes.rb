Freewar3::Application.routes.draw do 
  devise_for :members,
             controllers: {
               sessions: "sessions"
             }
  # :param => /.*/ prevents failing on a name with a period
  root :to => 'home#index'
  
  # ukimgs      
  get 'ukimgs/tag/:id/:tag_id/(up|down|report)',
        :as => 'ukimgs_tag',
        :to => 'ukimgs#tag'
      
  get 'ukimgs(/sets/:sets)(/tags/:tags)',
        :as => 'ukimgs',
        :to => 'ukimgs#index'
  
  # about
  scope :controller => :home, :path => '/' do
    get '/about', :action => :about, :as => 'about'
    get '/contact', :action => :contact, :as => 'contact'
    get '/dumps(/:path)', :action => :dumps, :as => 'dumps'
    get '/impressum', :action => :impressum, :as => 'impressum'
  end
  
  # single achievement
  get 'achievement/:name(/:stage)', 
        :as => 'achievement',
        :to => 'achievements#show'
  # Achievements
  scope :as => 'achievements', :controller => :achievements, :path => '/achievements' do
    get '/group_progress/:group/:users.:format',
          :action => 'group_progress',
          :as     => 'group_progress'
          
    get '/rank(/show/:ids)(/page/:page)(/order/:order/:by)(/:world)',
          :action => 'rank',
          :as     => 'rank'
    get '/unachieved(/:world)', 
          :action => 'unachieved',
          :as     => 'unachieved'
    
    get '(/:world)', 
        :action => 'index',
        :as     => 'index'
    root action: 'index'
  end
  
  # Areas  
  scope :as => 'areas', :controller => 'areas', :path => '/areas' do
    get '/:name/places', 
          :action => 'places',
          :as => 'places'
    get '/:name', 
          :action => 'show',
          :as => 'show'
  end
  
  
  # single clan
  get 'clan/:id/:world', 
        :as => 'clan', 
        :to => 'clans#show'
  # clans
  scope :as => 'clans', :controller => 'clans', :path => '/clans' do
    match '/:action(/page/:page)(/order/:order/:by)(/:world)(/from/:starttime/till/:endtime)(/like/*name)', 
          :defaults => {
            :page   => 1
          },
          :via => [:get, :post]
    root action: 'index'
  end
  
  # single extension
  get '/extensions/show/:id', :to => 'extensions#show', :as => 'extension'
  
  # extensions
  scope :as => 'extensions', :controller => :extensions, :path => '/extensions' do 
    get '/:action'
    
    root :action => :index
  end
  
      
  # graphs
  scope :as => 'graphs', :controller => :graphs, :path => '/graphs' do
    root :action => 'index'
    
    match '/achievements(/:mode)', 
          :action => 'achievements', 
          :as => 'achievements',
          :via => [:get, :post] 
    match '/statistics',
          action: 'statistics',
          as: 'statistics',
          via: [:get, :post]
  end
  
  # map
  scope :as => 'map', :controller => :map, :path => '/map' do
    post '',
         :action => 'index'
    get '(/:x,:y)(/radius/:radius)',
        :action => 'index',
        :as => 'root'

    get '/show/:x,:y',
        :action => 'show',
        :as => 'place'
  end
  
  #news
  get '/news' => 'feeds#news', :as => 'news_feed',
      :defaults => {
        :format => 'atom'
      }
  get '/news/:id', :to => 'home#show', :as => 'news'
  
  # npcs
  get '/npcs/api(.:format)', :to => 'npcs#api', :as => 'npcs_api'
  resources :npcs, only: [:index, :show]
  
  get '/spotlights', as: "spotlights", to: "spotlights#show"
  
  # slmania
  scope as: 'slmania', controller: :slmania, path: '/slmania' do
    root action: 'index', as: 'index'
    
    scope path: '/:member', as: 'user' do 
      get '/npc/:name',  action: 'evaluate_npc', as: 'npc'
      get '/item/:name', action: 'evaluate_item', as: 'item'
      get '/soul_capsule', action: 'soul_capsule', as: 'soul_capsule'
      
      root action: 'list_actions'
    end
  end
  
  # single statistic/
  get 'statistic/:name(/:world)', 
        :as => 'statistic',
        :to => 'statistics#show'
  
  # statistics
  scope :as => 'statistics', :controller => :statistics, :path => '/statistics' do
    root action: 'index'
    
    get '/:action(/:world)'
  end
  
  # tools
  scope :as => 'tools', :controller => 'tools', :path => 'tools' do
    match '/railpatterns(/:active_pattern)', :as => 'railpatterns', 
                                          :action => 'railpatterns',
                                          :defaults => {
                                            :active_pattern => "Lebensschnitt"
                                          },
                                          :via => [:get, :post]
    match '/ability_calc', :as => 'ability_calc', :action => 'ability_calc', :via => [:get, :post]
  end
  
  # single user
  get 'user/:name/:world', 
        :as => 'user', 
        :name => /.*/, 
        :to => 'users#show'
  # users
  scope :as => 'users', :controller => 'users', :path => 'users' do
    match '/:action(/page/:page)(/order/:order/:by)(/:world)(/race/:race)(/from/:starttime/till/:endtime)(/like/*name)', 
          :defaults => {
            :page   => 1
          },
          :via => [:get, :post]
    root action: 'index'
  end
 
  # adminpanel
  namespace :admin do 
    resources :achievements, :dumps, :members, :npcs, :news, :races, :worlds
    root :to => 'base#index'
  end
  
  match "*path", :to => "application#routing_error", :via => :all
end
