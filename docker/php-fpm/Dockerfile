FROM php:7.4-fpm

RUN apt-get update

RUN apt update && apt install --assume-yes libsqlite3-dev zlib1g-dev autoconf libzip-dev openssh-client git supervisor unzip libpng-dev libjpeg-dev libfreetype6-dev gnupg wget

RUN docker-php-ext-install ctype iconv pdo_mysql zip sockets bcmath calendar pdo_sqlite gd sysvsem

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

RUN pecl install xdebug-2.9.8 \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable = 1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart = 1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back = 1" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN touch /usr/local/etc/php/conf.d/uploads.ini \
    && echo "file_uploads = On" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 164M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "upload_max_filesize = 164M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 164M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini

RUN mkdir -p /app/files/converted && chmod -R 777 /app/files

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

WORKDIR /app