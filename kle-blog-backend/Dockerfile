FROM php:8.5-fpm


RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip


RUN docker-php-ext-configure intl && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]