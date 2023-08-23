FROM php:8.1-fpm-alpine

RUN apk add --update --no-cache bash chromium chromium-chromedriver

#RUN apk add --update --no-cache firefox
#RUN wget https://github.com/mozilla/geckodriver/releases/download/v0.24.0/geckodriver-v0.24.0-linux64.tar.gz
#RUN tar -xzf geckodriver-v0.24.0-linux64.tar.gz -C /usr/local/bin &&    rm geckodriver-v0.24.0-linux64.tar.gz
#RUN chmod +x /usr/local/bin/geckodriver


COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql && \
    install-php-extensions zip && \
    install-php-extensions intl

WORKDIR /var/www/pulsar

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
