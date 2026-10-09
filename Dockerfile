FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
COPY config/docker-apache.conf /etc/apache2/conf-available/kanto.conf
RUN a2enconf kanto
COPY config/docker-php.ini /usr/local/etc/php/conf.d/kanto.ini

ENV APP_BASE_PATH=/
COPY . /var/www/html/
WORKDIR /var/www/html/
RUN mkdir -p storage/registration && chown -R www-data:www-data /var/www/html
EXPOSE 80
