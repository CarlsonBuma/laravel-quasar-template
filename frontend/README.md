# Setup
## Initialization
 - npm install
 - Optional: Setup \src\modules\cookieConsent.js
     - Implement Cookies (eg. Google Tags, Bing UET, etc.)
 - Create Google Cloud Account (Geolocation)
    - Define API_KEY and activate Services
    - https://console.cloud.google.com/
 - Setup package.json
 - Edit .env file
    - incl. Third-Partie Integrations

## Third-Partie Integrations
[X] PaddleJS
    1. Setup Paddle Account
        - https://developer.paddle.com/paddlejs/overview
    2. Setup .env File
    3. Define Access-Token set by Paddle (.env)
        - Access is defining accessible UI Features
    4. Implement '@/components/PaddleSubscription.vue'

[X] Google Developer Services API
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
        - Global: "this.$cc"
        - Dok: https://github.com/eyecatchup/vue-cookieconsent
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

# Deployment
 - See Vue 3
 - Google API, restrict API-Token to current App-Url
