#!/bin/sh
set -e

echo "Deploying application ..."

    # Update codebase
     #git fetch --all
    git pull origin master

    # Install dependencies based on lock file
    #composer update --no-interaction --prefer-dist --optimize-autoloader

    # Migrate database
    php artisan migrate

    # Note: If you're using queue workers, this is the place to restart them.
    # ...

    # Clear cache
    php artisan optimize

    # Reload PHP to update opcache
    #echo "" | sudo -S service php7.4-fpm reload
# Exit maintenance mode

echo "Application deployed!"
