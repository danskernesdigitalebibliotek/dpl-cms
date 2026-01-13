FROM mlocati/php-extension-installer:2@sha256:b17b8107fe8480d5f88c7865b83bb121a344876272eb6b7c9e9f331c931695be AS php-extension-installer
FROM uselagoon/php-8.3-cli-drupal:latest

# Make sure that every build has unique assets.
# By setting the build name as an ARG the following layers are not cached.
ARG LAGOON_BUILD_NAME

COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/bin
RUN install-php-extensions dio && docker-php-ext-enable dio

COPY composer.* /app/
COPY assets /app/assets
COPY packages /app/packages
COPY patches /app/patches
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev
COPY . /app
RUN mkdir -p -v -m775 /app/web/sites/default/files

# Define where the Drupal Root is located
ENV WEBROOT=web
