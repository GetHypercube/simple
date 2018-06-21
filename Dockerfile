FROM php:7.1-fpm
ARG CREDENTIALS_GIT
ARG REPO=gitlab.digital.gob.cl/simple/simple
ARG DIRECTORY_PROJECT=/var/www/simple

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
    && npm i npm@latest -g \
    # Install composer
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    # Clone repository
    && git clone --depth=1 https://$CREDENTIALS_GIT@$REPO $DIRECTORY_PROJECT \
    # Change to directory Project
    && cd $DIRECTORY_PROJECT \
    # Install dependencies  from project
    && composer install \
    && npm install \
    # Install compatible updates to vulnerable dependencies JavaScript
    && npm audit fix --force \
    # Compile JavaScript
    && npm run production \
    # TO DO SEGPRES
    && find $DIRECTORY_PROJECT -type f -exec chmod 644 {} \; \
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
EXPOSE 9000
CMD ["php-fpm"]