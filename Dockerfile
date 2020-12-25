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

RUN apk add --no-cache git imagemagick
RUN  apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS imagemagick-dev libtool \
     && git clone https://github.com/Imagick/imagick \
     && cd imagick \
     && phpize && ./configure \
     && make \
     && make install \
     && apk del .phpize-deps && rm -rf imagick
RUN echo "extension=imagick.so" >> /usr/local/etc/php/conf.d/imagick.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./ /var/www
WORKDIR /var/www

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
    composer install --prefer-dist --no-progress; \
    composer clear-cache