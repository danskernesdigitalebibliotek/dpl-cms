FROM uselagoon/php-8.1-cli-drupal:latest

ARG LAGOON_BUILD_NAME

COPY composer.* /app/
COPY assets /app/assets
COPY packages /app/packages
COPY patches /app/patches
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev
COPY . /app
RUN mkdir -p -v -m775 /app/web/sites/default/files

RUN echo "build name: " ${LAGOON_BUILD_NAME} > build-name.txt

# Define where the Drupal Root is located
ENV WEBROOT=web
