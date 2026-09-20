#!/usr/bin/env bash
set -e
composer install
[ -f .env ] || cp .env.example .env
php artisan key:generate
if grep -q "YOUR_POSTGRES_PASSWORD" .env; then
  echo "Edit .env and set the PostgreSQL password, then run: php artisan migrate --seed && php artisan storage:link && php artisan serve"
  exit 0
fi
php artisan config:clear
php artisan migrate --seed
php artisan storage:link || true
php artisan serve
