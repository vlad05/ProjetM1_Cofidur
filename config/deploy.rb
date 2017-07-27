set :application, 'cofidur_projet'
set :repo_url, 'https://github.com/vlad05/ProjetM1_Cofidur.git'

set :ssh_user, 'root'
server '10.235.36.193', user: fetch(:ssh_user), roles: %w{web app db}



set :format, :pretty
set :log_level, :info
# set :log_level, :debug

set :composer_install_flags, '--no-dev --prefer-dist --no-interaction --optimize-autoloader'

set :linked_files, %w{app/config/parameters.yml}
set :linked_dirs, %w{app/logs web/uploads}

set :keep_releases, 3

after 'deploy:finishing', 'deploy:cleanup'



