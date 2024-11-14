# Laravel 12 - Framework
Website: https://laravel.com/

## Initialization
  - composer install / update
    > php artisan --version
    > composer clear cache
    > composer show "pgvector/pgvector" --alL
  - define .env File
  - Setup meta-data in package.json

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

## Additional Microservices
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

 # Live Deployment
 See Laravel 11 Docs & it's Dependencies
  - .env set to production
  - php artisan vendor:publish --tag=passport-config
    - Enter Client Secrets in .env File
  - composer install --optimize-autoloader --no-dev
  - php artisan config:cache
  - php artisan event:cache
  - php artisan route:cache
