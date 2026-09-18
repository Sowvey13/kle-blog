#!/bin/sh
set -e

cd /var/www

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ ! -d node_modules ]; then
    npm install
fi

if [ ! -d public/build ]; then
    npm run build
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -qE '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

exec php artisan serve --host=0.0.0.0 --port=8000
