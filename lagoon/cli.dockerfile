FROM uselagoon/php-8.0-cli-drupal:latest

COPY composer.* /app/
COPY assets /app/assets
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev
COPY . /app
RUN mkdir -p -v -m775 /app/web/sites/default/files

# Make a symlink ready for the module upload directory.
RUN ln -s /app/web/sites/default/files/modules_local /app/web/modules/local

# Define where the Drupal Root is located
ENV WEBROOT=web
