# Change access rights for the Laravel folders
# in order to make Laravel able to access
# cache and logs folder.
chgrp -R www-data storage bootstrap/cache && \
    chown -R www-data storage bootstrap/cache && \
    chmod -R ug+rwx storage bootstrap/cache && \
# Create log file for Laravel and give it write access
# www-data is a standard apache user that must have an
# access to the folder structure
touch storage/logs/laravel.log && \
chmod 775 storage/logs/laravel.log && \
chown www-data storage/logs/laravel.log

composer install && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan key:generate && \

#NPM
npm install && \
npm run prod && \

php artisan migrate:fresh --seed && \
php artisan elasticsearch:admin create && \
php artisan elasticsearch:admin index && \
echo "Done..."