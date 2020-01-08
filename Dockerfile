FROM php:7.1-fpm
ARG DIRECTORY_PROJECT=/var/www/simple

WORKDIR $DIRECTORY_PROJECT

# Install NewRelic
RUN  curl -L https://download.newrelic.com/php_agent/release/newrelic-php5-9.4.1.250-linux.tar.gz | tar -C /tmp -zx && \
   export NR_INSTALL_USE_CP_NOT_LN=1 && \
    export NR_INSTALL_SILENT=1 && \
     /tmp/newrelic-php5-*/newrelic-install install && \
      rm -rf /tmp/newrelic-php5-* /tmp/nrinstall* && \
        sed -i -e 's/"REPLACE_WITH_REAL_KEY"/"6eed51509d7ffe6e0a82db3994a63cbe14f6NRAL"/' \
     -e 's/newrelic.appname = "PHP Application"/newrelic.appname = "Simple2"/' \
         /usr/local/etc/php/conf.d/newrelic.ini

# Install Packages
RUN apt-get update && apt-get install -y \
 git zip unzip gnupg \
 libxml2-dev zip unzip zlib1g-dev \
 libpng-dev libmcrypt-dev \
 --no-install-recommends

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

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && apt-get remove --purge -y curl \
  && apt-get autoremove -y \
  && apt-get clean

COPY . $DIRECTORY_PROJECT

RUN composer install

RUN chown -R www-data:www-data storage/


RUN  ln -sf /dev/stderr /var/log/php-errors.log
#RUN  ln -sf /dev/stderr /var/www/simple/storage/logs/laravel.log

ENV LANG es_CL.UTF-8
ENV LANGUAGE es_CL:es
ENV LC_ALL es_CL.UTF-8
ENV TZ America/Santiago

WORKDIR $DIRECTORY_PROJECT

EXPOSE 9000
CMD ["php-fpm"]
