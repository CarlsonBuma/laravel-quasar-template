# Laravel 12 - Framework
Website: https://laravel.com/

## Initialization
  - composer install / update
    > php artisan --version
    > composer clear cache
    > composer show "pgvector/pgvector" --alL
  - define .env File
    - php artisan key:generate
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
  - add 'storage/app/public'
  - php artisan storage:link

### Setup Mail Driver
  - Choose your Mail Driver (according Serverhost)
  - Enter Attributes into .env file

# Additional Microservices
[X] Paddle - Payment Gateway
    - https://login.paddle.com/login
    - https://sandbox-login.paddle.com/login

## Setup Payment Gateway by Paddle
Setup Paddle Webhook Handling
   1. Install Ngrok (Reverse Proxy) - Webhooks
      - ngrok http http://127.0.0.1:8000
      - Check Webhooks: Ngrok Web Interface
   2. Login to Paddle Sandbox
      - Notifications: Setup Webhook URL
      - Paste Webhook Secret Key in .env file
   3. Define Prices
      - 'One-Time Purchase' vs. 'Subscription'
      - add custom attributes to price
         - 'access_token' (string): Allows to set flags according user app-access
         - 'duration_months' (int): Default Expiration (by 'One-Time Purchase')
            - Overwritten by paddles 'ends_at' (by 'Subscription')
      - Price seeding by Webhook ("Listeners/PaddleEventListener", @created, @updated')
    4. Adjust Files according your logic
      - Middleware: "/Middleware/PaddleWebhookVerification"
      - Webhook Listener: "/Listeners/PaddleWebhookListener"
        - transaction.completed
          - Add User Access
        - subscription.updated
        - price.created
        - transaction.payment_failed
      - Paddle Webhook Handling: "/Auth/AppAccess/*"
      - UserAccess: "/Auth/AppAccess/UserAccessController"

 # Live Deployment
 See Laravel 11 Docs & it's Dependencies
  - .env set to production
  - php artisan vendor:publish --tag=passport-config
    - Enter Client Secrets in .env File
  - composer install --optimize-autoloader --no-dev
  - php artisan config:cache
  - php artisan event:cache
  - php artisan route:cache
