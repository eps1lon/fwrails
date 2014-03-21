Freewar3::Application.routes.draw do 
  # :param => /.*/ prevents failing on a name with a period

  # ukimgs      
  get 'ukimgs/tag/:id/:tag_id/(up|down|report)',
        :as => 'ukimgs_tag',
        :to => 'ukimgs#tag'
      
  get 'ukimgs(/sets/:sets)(/tags/:tags)',
        :as => 'ukimgs',
        :to => 'ukimgs#index'
  
  # about
  scope :as => 'about', :controller => :about, :path => '' do
    get '/about', :action => :index
    get '/contact', :action => :contact, :as => 'contact'
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
          
    get '/index(/:world)', 
          :action => 'index',
          :as     => 'index'
          
    get '/rank(/:world)(/show/:ids)(/page/:page)(/order/:order)(/:by)',
          :action => 'rank',
          :as     => 'rank',
          :constraints => {
            :by => /(asc|desc)/i
          },
          :defaults => {
            :by    => 'desc',
            :ids   => '',
            :order => 'count',
            :page  => 1
          }
    get '/unachieved(/:world)', 
          :action => 'unachieved',
          :as     => 'unachieved'
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
    get '/:action(/:world)(/page/:page)(/order/:order)(/:by)(.:format)',  
        :constraints => {
          :by => /(asc|desc)/i
        },
        :defaults => {
          :by     => 'desc',
          :page   => 1
        },
        :name => /.*/
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
    match '/achievements(/:mode)', 
          :action => 'achievements', 
          :as => 'achievements',
          :via => [:get, :post]
    
    root :action => 'index'
  end
  
  # single statistic/
  get 'statistic/:name(/:world)', 
        :as => 'statistic',
        :to => 'statistics#show'
  
  # statistics
  scope :as => 'statistics', :controller => :statistics, :path => '/statistics' do
    get '/:action(/:world)'
  end
  
  # single user
  get 'user/:name/:world', 
        :as => 'user', 
        :name => /.*/, 
        :to => 'users#show'
  # users
  scope :as => 'users', :controller => 'users', :path => 'users' do
    match '/:action(/race/:race)(/like/:name)(/:world)(/page/:page)(/order/:order)(/:by)(.:format)', 
          :constraints => {
            :by => /(asc|desc)/i
          },
          :defaults => {
            :by     => 'desc',
            :page   => 1
          },
          :name => /.*/,
          :via => [:get, :post]
  end
 
  # adminpanel
  namespace :admin do 
    resources :achievements, :races, :worlds
    root :to => 'base#index'
  end
  
  root :to => 'home#index'
end
