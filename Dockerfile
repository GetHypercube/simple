FROM php:7.1-fpm
ARG CREDENTIALS_GIT
ARG REPO=gitlab.digital.gob.cl/simple/simple

RUN apt-get update \
&& apt-get install -y libxml2-dev git zip unzip zlib1g-dev libpng-dev libmcrypt-dev --no-install-recommends \
&& apt-get clean \
&& rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*  \
&& docker-php-ext-install opcache pdo_mysql pdo mbstring tokenizer xml ctype json zip soap mcrypt gd \
&& git clone --depth=1 https://$CREDENTIALS_GIT@$REPO /var/www/simple \
&& cd /var/www/simple \
&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& composer update \
&& composer dump-autoload -o \
&& rm -rf /root/.composer/cache/* \
&& rm -rf  dump.sql .gitattributes webpack.mix.js package-lock.json package.json phpunit.xml .env.example .git .gitignore composer* docker-compose.yml Dockerfile .gitlab-ci-yml readme.md tests /usr/local/bin/composer \
&& find /var/www/simple -type f -exec chmod 644 {} \; \
&& find /var/www/simple -type d -exec chmod 755 {} \; \
&& chown -R www-data:www-data /var/www/simple

ENV LANG es_CL.UTF-8
ENV LANGUAGE es_CL:es
ENV LC_ALL es_CL.UTF-8
ENV TZ America/Santiago

WORKDIR /var/www/simple
EXPOSE 9000
CMD ["php-fpm"]