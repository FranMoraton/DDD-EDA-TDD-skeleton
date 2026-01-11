FROM dunglas/frankenphp:1-php8.5-alpine AS base

ENV DATADOG_TRACE_VERSION=1.14.1

ENV PROJECT_DIR=/var/www
ENV PATH=$PROJECT_DIR/bin:$PROJECT_DIR/vendor/bin:$PATH
ENV SERVER_NAME=:80
ENV DOCUMENT_ROOT=$PROJECT_DIR/public

# The install-php-extensions script is available in the image
RUN install-php-extensions \
        pdo_pgsql  \
        opcache \
        intl \
        zip \
        xml \
        redis \
        amqp-2.2.0

COPY --from=composer:2.9.3 /usr/bin/composer /usr/bin/composer

COPY docker/php/config/php_prod.ini /usr/local/etc/php/php.ini
COPY docker/php/config/opcache_prod.ini /usr/local/etc/php/conf.d/opcache.ini

RUN curl -LO https://github.com/DataDog/dd-trace-php/releases/download/${DATADOG_TRACE_VERSION}/datadog-setup.php \
	&& php datadog-setup.php --php-bin=all

WORKDIR $PROJECT_DIR
RUN chown -R www-data:www-data $PROJECT_DIR

FROM base AS development
COPY docker/php/config/php.ini /usr/local/etc/php/php.ini
COPY docker/php/config/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN apk add --no-cache git bash && \
    install-php-extensions xdebug-3.5.0

FROM development AS ci
ENV APP_ENV=dev
COPY ./ $PROJECT_DIR
RUN composer install

FROM base AS result
ENV APP_ENV=prod
COPY ./ $PROJECT_DIR
RUN cp -f $PROJECT_DIR/docker/php/config/public.pem $PROJECT_DIR/config/jwt/public.pem
RUN composer install --no-dev --classmap-authoritative --optimize-autoloader
RUN rm -rf \
            docker \
            tests \
            .env.test \
            grumphp.yml \
            phpcs.xml.dist \
            phpstan.neon \
            phpunit.xml.dist

FROM base AS prod
COPY --from=result $PROJECT_DIR ./
RUN chown -R www-data:www-data $PROJECT_DIR
USER www-data