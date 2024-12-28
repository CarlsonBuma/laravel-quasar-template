'use strict';

// Default modules
import { ref } from 'vue';
import { boot } from 'quasar/wrappers';
import ResponseHandler from 'src/boot/modules/responseHandling.js';
import storeUser from "src/stores/user.js";
import globals from 'src/boot/modules/globals.js';

// Translations - l18n workaround
import translationPackage from './translations/index.js'

// Cookie Consent
import CookieConsent from 'vue-cookieconsent';
import CookieConsentOptions from 'src/boot/modules/cookieConsentOptions.js';
import 'vue-cookieconsent/vendor/cookieconsent.css';

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
import NavCockpit from 'src/components/navigation/NavCockpit.vue';
import NavAdmin from 'src/components/navigation/NavAdmin.vue';


export default boot(({ app, router }) => {
    
    // Env Variables
    app.config.globalProperties.$env = {
        APP_NAME: process.env.APP_NAME,
        APP_ACCESS_ADMIN: process.env.APP_ACCESS_ADMIN,
        APP_ACCESS_COCKPIT: process.env.APP_ACCESS_COCKPIT
    };
    
    // Defaults
    app.config.globalProperties.$drawerLeft = ref(false);
    app.config.globalProperties.$toast = new ResponseHandler(router, app);
    app.config.globalProperties.$user = storeUser();
    app.config.globalProperties.$globals = globals;

    // Translation Package
    app.config.globalProperties.$tp = translationPackage();
 
    // Cookie-Consent accessible by this.$cc
    app.use(CookieConsent);
    app.config.globalProperties.$cc.run(CookieConsentOptions);

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

    // Navigation
    app.component('NavUser', NavUser)
    app.component('NavCockpit', NavCockpit)
    app.component('NavAdmin', NavAdmin)

});
