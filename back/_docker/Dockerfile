FROM php:8.4.7-fpm-bookworm

RUN apt-get update && apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      libicu-dev \
      icu-devtools \
      zip unzip \
      git \
      iputils-ping \
      sqlite3 \
      libsqlite3-dev \
      redis-tools \
      nano \
      supervisor && \
      docker-php-ext-configure intl && \
      docker-php-ext-install pdo_mysql pdo_sqlite bcmath gd zip intl && \
      pecl install redis && \
      docker-php-ext-enable redis && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
      touch /var/log/php_error.log && chmod 777 /var/log/php_error.log

COPY ./_docker/php.ini /usr/local/etc/php/conf.d/php.ini

# install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

RUN mkdir -p /etc/supervisor/conf.d
COPY ./_docker/supervisor/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

WORKDIR /var/www/bitrix24.reutskiy-a.ru/