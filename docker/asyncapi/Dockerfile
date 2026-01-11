FROM node:20-alpine AS base

RUN apk add --no-cache \
    git \
    build-base \
    python3 \
    npm

RUN mkdir -p /scheme /www && \
    chmod -R 777 /scheme /www


RUN npm install -g @asyncapi/cli@2

RUN asyncapi config analytics --disable

# Skip Chromium download for arm64 compatibility (Puppeteer dependency)
ENV PUPPETEER_SKIP_DOWNLOAD=true

RUN npm install -g @asyncapi/html-template@3.0.0

COPY ./docs/asyncapi.yaml /scheme/asyncapi.yml

RUN asyncapi generate fromTemplate /scheme/asyncapi.yml @asyncapi/html-template@3.0.0 -o /www --use-new-generator --force-write

FROM nginx:1.24-alpine AS server
COPY --from=base /www /usr/share/nginx/html