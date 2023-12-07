FROM uselagoon/php-8.1-cli-drupal:latest

# ARG LAGOON_GIT_SHA
# ARG LAGOON_GIT_REF

COPY composer.* /app/
COPY assets /app/assets
COPY packages /app/packages
COPY patches /app/patches
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev
COPY . /app
RUN mkdir -p -v -m775 /app/web/sites/default/files

RUN printenv

RUN echo "----------------------------------- Mikdebug ------------------------------------"
RUN echo "sha: ${LAGOON_GIT_SHA}"
RUN echo "ref: ${LAGOON_GIT_REF}"
RUN echo "ref: ${LAGOON_BUILD_NAME}"
RUN echo "ref: ${TEMPORARY_IMAGE_NAME}"


# Define where the Drupal Root is located
ENV WEBROOT=web
