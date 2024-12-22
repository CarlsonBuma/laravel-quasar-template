# Project Overview
* Webhookhandling and access management
     * 
     ** Logic
     * Users can purchase products at a specified price to access various app features. 
     * These features are defined by the price token and expiration period, which are configured 
     * within the Paddle Cockpit. Our Webhook Listener listens for webhook calls to verify users 
     * and define access based on the defined logic.
     * 
     *  > Documentation: https://developer.paddle.com/webhooks/overview
     * 
     * 
     ** Initialization 
     * Access requests are initialized after client checkout via PaddleJS (UI-REST API call). 
     * This setup allows us to verify users for subsequent webhook calls.
     * 
     *  > Initial logic: "/Controllers/Access/UserAccessController::initializeClientCheckout(Request)"
     * 
     ** Setup 
     * Our webhooks correspond to the Paddle Cockpit and its correct configuration.
     *  1. Within Paddle, access tokens are defined by the prices users purchase, which grant access 
     *     to specific features in the app. Ensure including 'custom_data' in price configuration:
     *      > 'access_token' (required): Defines access to the app and its features.
     *      > 'duration_months': Defines the period of access.
     *          > Note: This is overridden by the subscription.billing_period.ends_at value
     *  3. Define access token based on the logic:
     *      > Define a new APP_ACCESS_TOKEN in your .env file, according to the price token.
     *      > Enable the current token within "/Controllers/Access/PaddlePriceHandler".
     *  4. Setup Webhook Gateway:
     *      > Webhook URL: https://sandbox-vendors.paddle.com/notifications
     *      > Endpoint: https://{URL}/access/webhook    
     *  5. Add logic according to access tokens within app.
     * 
     ** Important Note: 
     * The logic only considers the webhook calls that are defined within this handler. 
     * Any undefined webhook events must be handled and adjusted by the logic:
     *   > See Controllers: "/Access"
     *   > See Collections: "AccessCollection.php"
     *   > See Middleware: 
     *      > "AppAccessCockpit"
     *      > Add logic as needed.

# Setup: Laravel 12 - Framework
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
          - Remove User Access
      - Paddle Webhook Handling: "/Auth/AppAccess/*"
      - User Access Management: "/Auth/AppAccess/UserAccessController"

 # Live Deployment
 See Laravel 11 Docs & it's Dependencies
  - .env set to production
  - php artisan vendor:publish --tag=passport-config
    - Enter Client Secrets in .env File
  - composer install --optimize-autoloader --no-dev
  - php artisan config:cache
  - php artisan event:cache
  - php artisan route:cache
