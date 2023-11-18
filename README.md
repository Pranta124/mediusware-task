
## Prerequisites
PHP (>=7.2)
Composer
MySQL (or any supported database)

## Install and Migration

git clone git@github.com:Pranta124/mediusware-task.git
cd project-name
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve



