FROM php:7.1-fpm
ARG DIRECTORY_PROJECT=/var/www/simple

WORKDIR $DIRECTORY_PROJECT

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
    # Install Node.js v8
    && curl -sL https://deb.nodesource.com/setup_8.x | bash - \
    && apt-get install -yq nodejs build-essential \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && npm i npm@latest -g \
    && apt-get remove --purge -y curl \
    && apt-get autoremove -y \
    && apt-get clean \
    && apt-get autoclean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY . $DIRECTORY_PROJECT

RUN composer install
RUN npm install && npm run prod

RUN chown -R www-data:www-data storage/

# TO DO SEGPRES
#RUN find $DIRECTORY_PROJECT -type f -exec chmod 644 {} \; \
#    && find $DIRECTORY_PROJECT -type d -exec chmod 755 {} \; \
#    && chown -R www-data:www-data $DIRECTORY_PROJECT 

ENV LANG es_CL.UTF-8
ENV LANGUAGE es_CL:es
ENV LC_ALL es_CL.UTF-8
ENV TZ America/Santiago

WORKDIR $DIRECTORY_PROJECT

EXPOSE 9000
CMD ["php-fpm"]
