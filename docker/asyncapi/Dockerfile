FROM asyncapi/generator:2.5.0 AS base
RUN mkdir -p /scheme /www

COPY ./docs/asyncapi.yaml /scheme/asyncapi.yml
RUN ag /scheme/asyncapi.yml @asyncapi/html-template -o /www

FROM nginx:1.24-alpine AS server
COPY --from=base /www /usr/share/nginx/html