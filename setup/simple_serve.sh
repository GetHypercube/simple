composer install && \
php artisan config:clear && \
php artisan cache:clear && \
# load redis, rabbit, etc
npm run watch