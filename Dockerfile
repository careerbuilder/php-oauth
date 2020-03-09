ARG PHP_VERSION
FROM php:${PHP_VERSION:-7.4}-alpine
RUN apk add --no-cache git $PHPIZE_DEPS && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    curl -# https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer
COPY . .
RUN composer install --no-progress --prefer-dist --no-interaction --no-suggest
RUN php -v
ENTRYPOINT []
CMD ["php", "bin/phpunit", "--coverage-text", "--whitelist=./src", "tests"]
