FROM php:8-cli-alpine

#RUN apk add --no-cache libpng libpng-dev libjpeg-turbo-dev libjpeg libwebp-dev zlib-dev libxpm-dev 
#RUN docker-php-ext-install gd
#RUN apk del libpng-dev libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev
# mcrypt, gd, iconv
RUN apk add --update --no-cache \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/ \
    && docker-php-ext-install -j"$(getconf _NPROCESSORS_ONLN)" gd
    
#RUN apk add --update --no-cache autoconf g++ imagemagick imagemagick-dev libtool make pcre-dev \
#    && pecl install imagick \
#    && docker-php-ext-enable imagick \
#    && apk del autoconf g++ libtool make pcre-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./ /var/www
WORKDIR /var/www

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
    composer install --prefer-dist --no-progress; \
    composer clear-cache