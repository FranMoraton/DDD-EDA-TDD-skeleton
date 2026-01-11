FROM nginx:1.28-alpine AS base

RUN rm /etc/nginx/conf.d/default.conf
COPY  docker/nginx/config/nginx.conf /etc/nginx/nginx.conf
COPY  docker/nginx/config/php.conf /etc/nginx/templates/php.template

FROM base AS local
ENV PHP_HOST=php

FROM base AS prod
ENV PHP_HOST=127.0.0.1