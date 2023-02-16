FROM php:8.1-fpm-alpine

RUN apk add --update --no-cache bash chromium chromium-chromedriver
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql && \
    install-php-extensions zip && \
    install-php-extensions intl

WORKDIR /var/www/pulsar

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
