#!/bin/sh
set -e

php artisan config:clear

if ! php artisan migrate --force; then
  echo "Warning: migrations failed; starting server anyway."
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
