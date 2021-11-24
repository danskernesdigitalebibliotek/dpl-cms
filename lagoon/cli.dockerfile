FROM uselagoon/php-8.0-cli-drupal:latest

USER root

COPY composer.* /app/
COPY assets /app/assets
COPY patches /app/patches
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev
COPY . /app
RUN mkdir -p -v -m775 /app/web/sites/default/files

# Make a symlink ready for the module upload directory.
RUN ln -s /app/web/sites/default/files/modules_local /app/web/modules/local
RUN chmod -R g-w /app/web
RUN chown user /app/web/site/default/files

# Define where the Drupal Root is located
ENV WEBROOT=web

# Not actually used
USER user
