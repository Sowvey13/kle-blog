FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        curl \
        gnupg \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libsqlite3-dev \
        pkg-config \
        zip \
        unzip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

WORKDIR /var/www

EXPOSE 8000
ENTRYPOINT ["app-entrypoint.sh"]
CMD ["serve"]
