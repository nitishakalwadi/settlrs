settlrs

Install php 7 or higher

Install laravel passport
composer require paragonie/random_compat=~2.0
composer require laravel/passport=~4.0
add in boot of AppServiceProvider
\Schema::defaultStringLength(191);
php artisan migrate
php artisan passport:install
