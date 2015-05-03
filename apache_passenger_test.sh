export ORIG_PATH="$PATH"
  rvmsudo -E /bin/bash
  export PATH="$ORIG_PATH"
  /usr/local/rvm/gems/ruby-2.1.0/wrappers/ruby /usr/local/rvm/gems/ruby-2.1.0/gems/passenger-4.0.59/bin/passenger-config --detect-apache2

