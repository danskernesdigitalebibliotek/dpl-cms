ARG CLI_IMAGE
FROM mlocati/php-extension-installer:2@sha256:b17b8107fe8480d5f88c7865b83bb121a344876272eb6b7c9e9f331c931695be AS php-extension-installer
FROM ${CLI_IMAGE} as cli

FROM uselagoon/php-8.3-fpm:latest

COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/bin
RUN install-php-extensions dio && docker-php-ext-enable dio

COPY --from=cli /app /app
