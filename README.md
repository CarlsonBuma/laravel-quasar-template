### V.1.0, by Gigup Solutions
Web App Template
by Carlson, -v.2.0, 12.11.2024

# Microservices
 * Dev Environemnt:     Server::9003, UI::3000
 * Github:              Code Management
 * Mail Host:           Hosted Server
 * Paddle:              https://developer.paddle.com/build/overview
 * Google API:          https://developers.google.com

## Security Check
   - go "/frontend": 
      - npm update
      - npm audit
      - npm audit fix --force
   - go "/backend": 
      - composer outdated
      - composer update

## Folder Structure
Root:
   - /backend
      - see Readme.md
   - /documents
   - /frontend
      - see Readme.md
   - Start Environment - "start in terminal":
      > ./env-start.ps1
   - Setup Docker Environment - "docker compose up":
      > ./docker-compose.yml

# System Dependencies
Tutorial: https://www.youtube.com/watch?v=Jdg9x3BDT38
Root:
- Backend (Laravel 11 + Database)
   > PHP 8.3 Compiler
   > User Auth: Laravel Passport (Oauth 2.0)
   > DB: PSQL - pgvector/pgvector:latest 
      > implements Extension "vectors"
   > Paddle Paymentgateway (See AccessFiles + PaddleJS)
      > https://developer.paddle.com/build/overview
- Frontend (Quasar CLI - Vue3 Framework)
   > Quasar "$quasar info"
   > Cookie Consent (cookieConsent.js)
      > https://github.com/eyecatchup/vue-cookieconsent
   > Google API - Geolocation
      > https://developers.google.com/maps/documentation/geocoding/start
      > https://developers.google.com/maps/documentation/javascript?hl=de
      > https://developers.google.com/maps/documentation/geocoding/get-api-key?hl=de

## Testing Environment
   - Install Ngrok (Reverse Proxy) - Webhooks
      - ngrok http http://127.0.0.1:8000
      - ngrok http http://localhost:9000/#

## Setup Docker Environment
   - Docker Setup Environment - "docker-compose up -d"
   - Initiate Docker "root/docker-compose.yml"
      - Vector Database PostgreSQL: Latest
      - PG_Admin: Latest
      - PHP:Latest

### Local Backend Compiler (PHP)
Instead of Docker, use a local Backend Compiler
   - Install newest PHP Compiler
   - Setup Environment Variables "Path/to/php.exe"
   - Setup php.ini (Development) file by
      > Variables
      > Extensions
   - PHP - Xdebug
      > Download php_xdebug
      > Adjust php.ini file
         zend_extension="B:\PHP-Composer\php-8.3.9\ext\php_xdebug.dll"
         xdebug.mode=debug
         xdebug.start_with_request=yes
         xdebug.client_port=9003
         xdebug.client_host=127.0.0.1
         xdebug.connect_timeout_ms=200
