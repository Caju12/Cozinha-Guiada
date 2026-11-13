# PHP + Apache container. SQLite data must be mounted at /var/www/html/storage.
FROM php:8.3-apache

# Install development headers and pkg-config before compiling PHP extensions.
# mbstring is needed by input validation; pdo_sqlite/pdo_mysql power database access.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev pkg-config libonig-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod headers

WORKDIR /var/www/html
COPY . /var/www/html
RUN mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/storage \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
ENV APP_ENV=production DB_DRIVER=sqlite DB_NAME=storage/cozinha_guiada.sqlite
EXPOSE 80
