FROM php:7.4-fpm-alpine

RUN apk add --update --no-cache bash chromium chromium-chromedriver
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql && \
    install-php-extensions zip && \
    install-php-extensions intl

WORKDIR /var/www/pulsar

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony