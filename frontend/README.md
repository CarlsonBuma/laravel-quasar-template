# Quasar CLI - Framework
Website: https://quasar.dev/introduction-to-quasar

## Initialization
 - npm install
    > quasar info         // Current dependencies
    > quasar dev          // Dev Mode
    > quasar build        // Production Mode
    > npm run lint
    > npm run format
 - Define .env file
 - Setup meta-data in package.json
 - Optional: Setup \src\modules\cookieConsent.js
    > Implement Cookies (eg. Google Tags, Bing UET, etc.)

# Additional Microservices
## PaddleJS
    1. Setup Paddle Account
        - https://developer.paddle.com/paddlejs/overview
    2. Setup .env File
    4. Implement '@/components/PaddleSubscription.vue'
    5. Handle PaddleJS according Logic
        - Prices and Tokens will be provided by our server
        - or define them manually in UI
    6. Initialize Client Checkout
        - see backend/"UserCheckoutController"

## Google Developer Services API
    1. Create Google Developers Account
        - Google Maps: Geolocation
            - https://developers.google.com/maps/documentation/geocoding/start 
        - Google Maps: Map Implementation    
            - https://github.com/inocan-group/vue3-google-map
            - https://developers.google.com/maps/documentation/javascript?hl=de
    2. Setup .env File
    3. Implement '@/components/GoogleLocations.vue' 
        
[X] Cookie-Consent (GDPR, for Google Analytics, Bing UET, etc.)
    1. Define Cookies in '@modules/cookieConsent.js'
        - Dok: https://github.com/eyecatchup/vue-cookieconsent
        - Global: "this.$cc"
        - Implemented: "App.js" & "quasar.config.js" (as boot-file)
        - Module '@modules/cookieConsent.js"
        - Implementes Cookies: 
            - cc_consent (by Cookie-Consent)
            - Google Analytics / Tag Manager included
                - Setup Tag Manager Account (Google Tag Manager)
                - Import Script to Bootfiles "cookie-consens.js"
                - Define in Cookie-Consent

[X] QR Code
    - https://github.com/scopewu/qrcode.vue


# Live Deployment
 - See Vue 3 / Quasar Framework
 - Google API, restrict API-Token to current App-Url
