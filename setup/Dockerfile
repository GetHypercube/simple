FROM php:7.1-fpm
ARG DIRECTORY_PROJECT=/var/www/simple

RUN alias ll='ls -la'

WORKDIR $DIRECTORY_PROJECT

# Install Packages
RUN apt-get update && apt-get install -y \
 git zip unzip gnupg nginx curl apt-utils nano libpq-dev \
  mariadb-client nmap net-tools iputils-ping \
 libxml2-dev zip unzip zlib1g-dev \
 libpng-dev libmcrypt-dev libzip-dev
 # \
 #--no-install-recommends

# Docker extension install
RUN docker-php-ext-install \
  opcache \
  pdo_mysql \
  pdo \
  mbstring \
  tokenizer \
  xml \
  ctype \
  json \
  zip \
  soap \
  mcrypt \
  gd \
  bcmath \
  sockets

RUN docker-php-ext-enable pdo_mysql mcrypt

# Configuraciones PHP
RUN echo "\
log_errors = On\n\
error_log = /dev/stderr\n\
error_reporting = E_ALL\n\
post_max_size = 100M\n\
upload_max_filesize = 100M\n\
memory_limit = 512M\n\
max_input_vars = 2000\n\
date.timezone = "America/La_Paz"\n\
max_execution_time = 12000s" > /usr/local/etc/php/php.ini


# Shortcut to replace enabled vhost in nginx
RUN { \
        echo 'server {'; \
        echo '    listen 80;'; \
        echo '    server_name _;'; \
        echo '    root /var/www/simple/public;'; \
        echo '    include /etc/nginx/default.d/*.conf;'; \
        echo '    index index.php;'; \
        echo '    error_log /var/log/nginx/error.log;'; \
        echo '    client_max_body_size 300m;'; \
        echo '    location / {'; \
        echo '        try_files $uri $uri/ /index.php?$query_string;'; \
        echo '    }'; \
        echo '    location ~ [^/]\.php(/|$) {'; \
        echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;'; \
        echo '        fastcgi_index index.php;'; \
        echo '        fastcgi_split_path_info ^(.+?\.php)(/.*)$;'; \
        echo '        fastcgi_read_timeout 3000;'; \
        echo '        # Mitigate https://httpoxy.org/ vulnerabilities'; \
        echo '        fastcgi_param HTTP_PROXY "";'; \
        echo '        fastcgi_pass 127.0.0.1:9000;'; \
        echo '        include fastcgi_params;'; \
        echo '        include fastcgi.conf;'; \
        echo '        fastcgi_param APPLICATION_ENV local;'; \
        echo '    }'; \
        echo '}'; \
    } > /etc/nginx/sites-available/default



# Init services or another executions on build time
RUN { \
        echo '#!/bin/bash'; \
        echo 'service nginx start'; \
        echo 'php-fpm'; \
    } > /usr/local/bin/entrypoint

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chmod +x /usr/local/bin/entrypoint

#RUN chown -R www-data:www-data storage/

ENV LANG es_CL.UTF-8
ENV LANGUAGE es_CL:es
ENV LC_ALL es_CL.UTF-8
ENV TZ America/Santiago

WORKDIR $DIRECTORY_PROJECT

RUN echo "APP_KEY=$(php artisan key:generate --show)" > .env

EXPOSE 80 9000

# Services initialization (bash exec)
ENTRYPOINT ["entrypoint"]
#CMD ["php-fpm"]




