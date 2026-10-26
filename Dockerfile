FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
COPY config/docker-apache.conf /etc/apache2/conf-available/kanto.conf
RUN a2enconf kanto
COPY config/docker-php.ini /usr/local/etc/php/conf.d/kanto.ini

ENV APP_BASE_PATH=/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY . /var/www/html/
WORKDIR /var/www/html/
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
RUN mkdir -p storage/registration && chown -R www-data:www-data /var/www/html
EXPOSE 80

# UC17 reads phienthanhtoan when an unpaid invoice is opened. Prepare the
# additive, idempotent table before Apache accepts requests.
CMD ["sh", "-c", "php scripts/migrate-payment-links.php && php scripts/migrate-approval-email.php && exec apache2-foreground"]
