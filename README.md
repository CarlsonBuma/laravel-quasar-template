### V.1.0, by Gigup Solutions
### Webapp - Collabris 
by Carlson, -v.1.0, 15.08.2024

# Hosting
 * Github:      Code Management
 * Localhost:   Server & Domain
 * Open AI:     https://openai.com/ 
 * Metamask:    https://metamask.io/ 
 * Mail Host:   Hosted Server
 * Paddle:      https://developer.paddle.com/build/overview
 * Google API:  https://developers.google.com

# Docs & Tutorials
Setup Gitrepo: https://www.youtube.com/watch?v=Jdg9x3BDT38

## Security Check
   - go "/frontend": 
      - npm audit
      - npm audit fix --force
   - go "/backend": 
      - composer outdated
      - composer update

## Folder Structure
 - backend
    - see Readme.md
 - documents
 - frontend
    - see Readme.md

## System Dependencies
- Backend (Laravel 11 + Database)
   - User Auth: Laravel Passport (Oauth 2.0)
   - DB: PSQL - pgvector/pgvector:latest 
      - implements Extension "vectors"
   - Paddle Paymentgateway (See AccessFiles + PaddleJS)
      - https://developer.paddle.com/build/overview
- Frontend (Quasar - Vue3 Framework)
   - Quasar (2.12.0)
   - Axios (API Request)
      - Toast Request Handling (src/modules/responseHandling.js)
   - Pinia (User Store / Session Store)
   - Cookie Consent (cookieConsent.js)
      - https://github.com/eyecatchup/vue-cookieconsent
   - Google API - Geolocation
      - https://developers.google.com/maps/documentation/geocoding/start
      - https://developers.google.com/maps/documentation/javascript?hl=de
      - https://developers.google.com/maps/documentation/geocoding/get-api-key?hl=de

# Development
## Environment 
 - php 8.3 ("php -v")
     - Xdebug from Zend Engine v.4.1
     - composer v.2.4.1
 - node.js 18.7
 - docker 20.10
     - docker-desktop
     - pgvector/pgvector, pgadmin4
 - powershell

## Testing Environment
  - Ngrok (Reverse Proxy) - Webhooks
    - ngrok http http://127.0.0.1:8000
    - ngrok http http://localhost:9000/#

## Files
 - Start Environment - "start in terminal":
    - ./env-start.ps1
 - Setup Docker Environment - "docker compose up":
    - ./docker-compose.yml
