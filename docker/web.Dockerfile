FROM php:7.4-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

WORKDIR /var/www/pulsar

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony
RUN git config --global user.email "jeremy.dulon@live.fr" \
    && git config --global user.name "JeremyDulon"

## Chromium and ChromeDriver
#ENV PANTHER_NO_SANDBOX 1
## Not mandatory, but recommended
#ENV PANTHER_CHROME_ARGUMENTS='--disable-dev-shm-usage'
#RUN apk add --no-cache chromium chromium-chromedriver