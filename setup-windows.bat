@echo off
setlocal
where composer >nul 2>nul
if errorlevel 1 (
  echo Composer is not installed or not in PATH.
  pause
  exit /b 1
)
php -m | findstr /I "pdo_pgsql" >nul
if errorlevel 1 (
  echo PostgreSQL PHP driver is not enabled.
  echo Open your active php.ini ^(run: php --ini^) and enable:
  echo extension=pdo_pgsql
  echo extension=pgsql
  pause
  exit /b 1
)
composer install
if not exist .env copy .env.example .env
php artisan key:generate
findstr /C:"YOUR_POSTGRES_PASSWORD" .env >nul
if not errorlevel 1 (
  echo.
  echo STOP: Edit .env and set your real PostgreSQL DB_PASSWORD first.
  echo Also set MAIL_PASS before testing Forgot Password OTP.
  pause
  exit /b 0
)
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
if errorlevel 1 (
  echo Setup failed. Check the error above.
  pause
  exit /b 1
)
echo.
echo Setup complete. Starting http://127.0.0.1:8000
php artisan serve
