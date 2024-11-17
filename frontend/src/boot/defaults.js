'use strict';

// Default modules
import { ref } from 'vue';
import { boot } from 'quasar/wrappers';
import { ResponseHandler } from 'src/boot/responseHandling.js';
import storeUser from "src/stores/user.js";
import CookieConsent from 'vue-cookieconsent';

// Global compnents
import PageWrapper from 'src/components/global/PageWrapper.vue';
import PageDrawer from 'src/components/global/PageDrawer.vue';
import CardSimple from 'src/components/global/CardSimple.vue';
import FormWrapper from 'src/components/global/FormWrapper.vue';
import DialogWrapper from 'src/components/global/DialogWrapper.vue';
import LoadingData from 'src/components/global/LoadingData.vue';
import NoData from 'src/components/global/NoData.vue';
import SectionTitle from 'components/global/SectionTitle.vue';
import SectionNote from 'src/components/global/SectionNote.vue';
import SectionSplit from 'src/components/global/SectionSplit.vue';
import SectionSplitFix from 'src/components/global/SectionSplitFix.vue';
import SectionDesignDefault from 'src/components/global/SectionDesignDefault.vue';
import SectionDesignClear from 'src/components/global/SectionDesignClear.vue';
import SectionDesignColored from 'src/components/global/SectionDesignColored.vue';

// Navigations
import NavUser from 'src/components/navigation/NavUser.vue';
import NavAdmin from 'src/components/navigation/NavAdmin.vue';


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
        APP_ACCESS_COCKPIT: process.env.APP_ACCESS_COCKPIT
    };
    
    // Defaults
    app.config.globalProperties.$allowAuth = true;
    app.config.globalProperties.$user = storeUser();
    app.config.globalProperties.$toast = new ResponseHandler(router, app);
    app.config.globalProperties.$drawerLeft = ref(false);
 
    // Cookie-Consent accessible by this.$cc
    // Init Options (APP.VUE): this.$cc.run(this.$cookieConsentOptions)
    app.use(CookieConsent);

    // Glboal Components
    app.component('PageWrapper', PageWrapper)
    app.component('PageDrawer', PageDrawer)
    app.component('CardSimple', CardSimple)
    app.component('FormWrapper', FormWrapper)
    app.component('DialogWrapper', DialogWrapper)
    app.component('LoadingData', LoadingData)
    app.component('NoData', NoData)
    app.component('SectionTitle', SectionTitle)
    app.component('SectionNote', SectionNote)
    app.component('SectionSplit', SectionSplit)
    app.component('SectionSplitFix', SectionSplitFix)
    app.component('SectionDesignDefault', SectionDesignDefault)
    app.component('SectionDesignClear', SectionDesignClear)
    app.component('SectionDesignColored', SectionDesignColored)
    app.component('NavUser', NavUser)
    app.component('NavAdmin', NavAdmin)

});
