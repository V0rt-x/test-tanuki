FROM php:8.2-fpm-bullseye

ARG WWWGROUP=1000
ARG WWWUSER=1000

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=Europe/Moscow

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y \
        nginx \
        curl \
        wget \
        git \
        zip \
        unzip \
        libfreetype6-dev \
        libcap2-bin \
        libonig-dev \
        libpq-dev \
        libzip-dev \
        libxml2-dev \
        libssl-dev

RUN docker-php-ext-install -j$(nproc) \
        sockets \
        iconv \
        mbstring \
        pgsql \
        pdo \
        pdo_pgsql \
        opcache \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN groupadd -g $WWWGROUP www
RUN useradd -u $WWWUSER -ms /bin/bash -g www www
RUN useradd --no-create-home nginx
RUN usermod -u $WWWUSER -o nginx && groupmod -g $WWWGROUP -o nginx

RUN mkdir -p /run/php
RUN chown -R www:www /run/php
RUN chown -R www:www /var/www/html

WORKDIR /var/www/html

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

COPY docker/php/php.ini /usr/local/etc/php/conf.d/40-custom.ini
COPY docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
RUN echo "" > /usr/local/etc/php-fpm.d/www.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
ENTRYPOINT  ["entrypoint.sh"]
