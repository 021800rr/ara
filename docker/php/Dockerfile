FROM php:8.3-fpm

RUN ln -s /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

RUN apt-get update && apt install -y \
    libzip-dev zip libicu-dev libpq-dev wget acl git libxslt1-dev procps \
    librabbitmq-dev \
    libssh-dev \
    && docker-php-ext-install \
    bcmath \
    sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp

RUN docker-php-ext-install xsl

RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN docker-php-ext-install sockets

RUN docker-php-ext-install zip

RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN docker-php-ext-install pdo
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && docker-php-ext-install pdo_pgsql pgsql

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN pecl install apcu \
    && docker-php-ext-enable apcu

RUN docker-php-ext-install opcache \
    && docker-php-ext-enable opcache \
    && echo 'opcache.interned_strings_buffer=16' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.load_comments=Off' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.max_accelerated_files=16000' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.save_comments=Off' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

COPY --from=composer/composer:2.4.1 /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /var/www/public
