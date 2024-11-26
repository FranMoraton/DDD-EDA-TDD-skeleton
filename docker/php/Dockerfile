FROM php:8.3-fpm-alpine3.20 AS base

ENV PROJECT_DIR /var/www
ENV PATH $PROJECT_DIR/bin:$PROJECT_DIR/vendor/bin:$PATH

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

RUN apk add --no-cache openssl-dev \
    && install-php-extensions \
        pdo_mysql-stable \
        opcache-stable\
        intl-stable \
        zip-stable \
        @composer-2.8.3 \
        amqp-1.11.0 && \
    rm -rf /tmp/pear

COPY docker/php/config/php.ini /usr/local/etc/php/
COPY docker/php/config/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/config/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR $PROJECT_DIR
RUN chown -R www-data:www-data $PROJECT_DIR

EXPOSE 9000


FROM base AS development
COPY docker/php/config/php.ini /usr/local/etc/php/php.ini
COPY docker/php/config/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN apk add --no-cache git bash && \
    install-php-extensions xdebug-3.3.2 && \
    rm -rf /tmp/pear