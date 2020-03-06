FROM php:7-alpine
RUN apk add --no-cache git $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug
RUN curl -# https://getcomposer.org/installer | php
    && mv composer.phar /usr/local/bin/composer
COPY . .
RUN composer install --no-progress --prefer-dist --no-interaction --no-suggest
ENTRYPOINT []
CMD ["php", "bin/phpunit", "--coverage-text", "--whitelist=./src", "tests"]
