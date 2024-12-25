# Project Overview
## Setup: Laravel 12 - Framework
Website: https://laravel.com/

## Initialization
  - composer install / update
    > php artisan --version
    > composer clear cache
    > composer show "pgvector/pgvector" --alL
  - define .env File
    - php artisan key:generate
  - Setup meta-data in package.json

### DB Migrate Data
  - php artisan migrate
  - php artisan passport:install --force
  - Enter Client Secrets in .env File

### DB Seeding
  - php artisan db:seed --class=UserSeeder

### Storage Setup
  - add 'storage/app/public'
  - php artisan storage:link

### Setup Mail Driver
  - Choose your Mail Driver (according Serverhost)
  - Enter Attributes into .env file

# Additional Microservices
## Paddle Payment Provider
Manages client payments and interacts with our app (via backend webhooks) to verify user access, according purchased prices.
   1. Webhook testing: Install Ngrok (Reverse Proxy)
      - ngrok http http://127.0.0.1:8000
      - Check Webhooks: Ngrok Web Interface
   2. Setup Paddle Account
      1. Define Prices in Paddle and App, allowing feature access within app
        - see "\Controllers\Access\PaddlePriceHandler"
      2. Define Paddle Notification Webhooks
        - see "\routes\web"
        - Paste Webhook Secret Key in .env file
      3. Set other Paddle variables in .env file
   3. Adjust logic (optional)
      - Middleware: "\Middleware\"
      - Webhook Listener: "\Listeners\PaddleWebhookListener"
      - Access Management: "\Controllers\Access\"

 # Live Deployment
 See Laravel 11 Docs & it's Dependencies
  - .env set to production
  - php artisan vendor:publish --tag=passport-config
    - Enter Client Secrets in .env File
  - composer install --optimize-autoloader --no-dev
  - php artisan config:cache
  - php artisan event:cache
  - php artisan route:cache
