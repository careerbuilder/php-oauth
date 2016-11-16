FROM php:7-alpine

RUN echo "@testing http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories && \
    apk --update add php7-xdebug@testing && \
    rm -rf /var/cache/apk/* && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    echo "zend_extension=/usr/lib/php7/modules/xdebug.so" >> /usr/local/etc/php/conf.d/php.ini

RUN adduser -D php
USER php
ENV HOME=/home/php
RUN mkdir -p /home/php/app
WORKDIR /home/php/app

COPY composer.json ./
RUN composer install --prefer-dist && \
    rm -rf ~/.composer
ENV PATH=$PATH:/home/php/app/vendor/bin

COPY . .

CMD phpunit \
    --bootstrap vendor/autoload.php \
    --whitelist ./src \
    --coverage-text \
    --verbose \
    tests
