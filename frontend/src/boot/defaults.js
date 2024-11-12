'use strict';

import { ref } from 'vue';
import { boot } from 'quasar/wrappers';
import { ResponseHandler } from 'src/boot/responseHandling.js';
import storeUser from "src/stores/user.js";
import CookieConsent from 'vue-cookieconsent';
import PageWrapper from 'components/PageWrapper.vue';
import CardSimple from 'components/CardSimple.vue';
import DialogWrapper from 'components/DialogWrapper.vue';
import NoData from 'components/NoData.vue';


export default boot(({ app, router }) => {
    
    // Env Variable
    app.config.globalProperties.$env = {
        SESSION_NAME: process.env.APP_SESSION_NAME,
        APP_URL: process.env.APP_URL,
        APP_NAME: process.env.APP_NAME,
        APP_SLOGAN: process.env.APP_SLOGAN,
        APP_API_URL: process.env.APP_API_URL,
        APP_GOOGLE_API_KEY: process.env.APP_GOOGLE_API_KEY,
        APP_PADDLE_ENVIRONMENT: process.env.APP_PADDLE_ENVIRONMENT,
        APP_PADDLE_PUBLIC_KEY: process.env.APP_PADDLE_PUBLIC_KEY,
        APP_PADDLE_PRICE_BUSINESS_COCKPIT: process.env.APP_PADDLE_PRICE_BUSINESS_COCKPIT,
        APP_ACCESS_BUSINESS_COCKPIT: process.env.APP_ACCESS_BUSINESS_COCKPIT
    };
    
    // Defaults
    app.config.globalProperties.$allowAuth = true;
    app.config.globalProperties.$user = storeUser();
    app.config.globalProperties.$toast = new ResponseHandler(router, app);
    app.config.globalProperties.$drawerLeft = ref(false);

    /** 
     ** Cookie-Consent accessible by this.$cc
     **  > Init Options (APP.VUE): this.$cc.run(this.$cookieConsentOptions)
     */
    app.use(CookieConsent);

    // Glboal Components
    app.component('PageWrapper', PageWrapper)
    app.component('CardSimple', CardSimple)
    app.component('DialogWrapper', DialogWrapper)
    app.component('NoData', NoData)
});
