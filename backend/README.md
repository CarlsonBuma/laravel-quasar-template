# Setup
## Initialization
  - php artisan --version
  - composer clear cache
  - composer install / update
  - composer show "pgvector/pgvector" --alL
  - create .env File
  - adjust .env File & composer.json

### Setup Database (DOCKER)
As we want to allow AI/Vectors, we need first install the pgvector/pgvector:latest over Docker
  - See database/docker-compose.yml
  - Add Extension "vectors" to DB - Server
  - otherwiese, remove migration "0-00000-create_vector_extension".php, before seeding DB

### DB Migrate Data
  - php artisan migrate
  - php artisan passport:install --force
  - Enter Client Secrets in .env File

### DB Seeding
  - php artisan db:seed --class=UserSeeder

### Storage (some image must exist to link correctly!)
  - php artisan storage:link

### Setup Mail Driver
  - Choose your Mail Driver (according Serverhost)
  - Enter Attributes into .env file

## Third-Partie Integrations
[X] Paddle - Payment Gateway
    - https://login.paddle.com/login
    - https://sandbox-login.paddle.com/login
  1. Setup .env File to connect with Paddle
  2. Define custom-data, according to Paddles in product-price ("custom_data")
    - access_token: "define-token"
    - duration_months: int
  3. Adjust Files according your needs
    - Webhook Listener: "Listeners/PaddleEventListerner"
    - User Access Management: "Access/UserAccessController"
    - Paddle Handler: "Auth/AccessHandling"

 # Deployment
 See Laravel 10 Docs & it's Dependencies
  - .env set to production
  - php artisan vendor:publish --tag=passport-config
    - Enter Client Secrets in .env File
  - composer install --optimize-autoloader --no-dev
  - php artisan config:cache
  - php artisan event:cache
  - php artisan route:cache

# Local Development environment 
Backend (PHP 8.3), by Client Rest-API Calls
## Docker Environment 
  - Docker Setup Environment - "docker-compose up -d"
    - Initiate Docker "root/docker-compose.yml"
      - Vector Database PostgreSQL: Latest
      - PG_Admin: Latest
      - PHP:Latest
## Local Backend Compiler (PHP)
  - PHP install newest Compiler
    - Setup Environment Variables "Path/to/php.exe"
    - Setup php.ini (Development) file by
      - Variables
      - Extensions
    - PHP - Xdebug
      - Download php_xdebug
      - Adjust php.ini file
          zend_extension="B:\PHP-Composer\php-8.3.9\ext\php_xdebug.dll"
          xdebug.mode=debug
          xdebug.start_with_request=yes
          xdebug.client_port=9003
          xdebug.client_host=127.0.0.1
          xdebug.connect_timeout_ms=200
