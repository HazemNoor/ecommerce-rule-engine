FROM php:7.4-fpm-alpine

RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions xdebug @composer

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

ARG OWNER_USER_ID
ARG OWNER_GROUP_ID
RUN apk --no-cache add openssh git shadow
RUN groupmod -g ${OWNER_GROUP_ID} www-data && usermod -u ${OWNER_USER_ID} -d /var/www www-data

WORKDIR /var/www/html
