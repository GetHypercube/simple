FROM php:7.1-fpm
ARG CI_JOB_TOKEN
ARG CI_DEPLOY_USER
ARG CI_DEPLOY_PASSWORD
ARG REPO=git.gob.cl/simple/simple
ARG DIRECTORY_PROJECT=/var/www/simple
ARG DB_HOST
ARG DB_USERNAME
ARG DB_PASSWORD
ARG DB_DATABASE

COPY . $DIRECTORY_PROJECT

# Install Packages
RUN apt-get update && apt-get install -y \
        libxml2-dev \
        git \
        zip \
        unzip \
        zlib1g-dev \
        libpng-dev \
        libmcrypt-dev \
        gnupg \
        --no-install-recommends \
    # Docker extension install
    && docker-php-ext-install \
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
    # Upgrade to the latest version of npm
    && npm i npm@latest -g 
    # Install composer
    RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 
    # Change to directory Project
    RUN cd $DIRECTORY_PROJECT \
    # Install dependencies  from project
    && composer install -vvv
    RUN npm install 
    RUN npm run prod
    # TO DO SEGPRES
    RUN find $DIRECTORY_PROJECT -type f -exec chmod 644 {} \; \
    && find $DIRECTORY_PROJECT -type d -exec chmod 755 {} \; \
    && chown -R www-data:www-data $DIRECTORY_PROJECT \
    && apt-get remove --purge -y git curl \
    && apt-get autoremove -y \
    && apt-get clean \
    && apt-get autoclean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV LANG es_CL.UTF-8
ENV LANGUAGE es_CL:es
ENV LC_ALL es_CL.UTF-8
ENV TZ America/Santiago

WORKDIR $DIRECTORY_PROJECT

RUN echo "APP_KEY=$(php artisan key:generate --show)" > .env

EXPOSE 9000
CMD ["php-fpm"]