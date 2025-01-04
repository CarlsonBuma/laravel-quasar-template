"use strict";
import { ref } from 'vue';
import { Cookies } from 'quasar'
import dateFormat from './formats/date.js';
import languagePack from './lang/index.js';

// Set Cookie 'client_
// Cookie Consent: Required by system
const setCookie = (name, format) => {
    Cookies.set(name, format, {
        secure: true,
        expires: '160'
    })
    
    return Cookies.get(name) ?? format
}

export default () => {

    // Client provides translation options
    const clientTranslationOptions = {
        'date': ['international', 'eu', 'us', 'test'] ,
        'lang': ['de', 'en', 'error']
    };

    // Client can choose translation settings
    // We store settings as client cookies
    const clientTranslationSettings = ref({
        dateFormat: Cookies.get('client_dateformat') 
            ?? setCookie('client_dateformat', 'international'),        
        language: Cookies.get('client_language') 
            ?? setCookie('client_language', 'en'),  
    });

    // Return Package
    return {
        'set_cookie': (name, format) => setCookie(name, format),
        'client_options': clientTranslationOptions,
        'client_settings': clientTranslationSettings,
        'date': (rawDate) => dateFormat[clientTranslationSettings.value.dateFormat]
            ? dateFormat[clientTranslationSettings.value.dateFormat](rawDate) 
            : 'undefined',
        'lang': (key) => languagePack[clientTranslationSettings.value.language] && languagePack[clientTranslationSettings.value.language][key]
            ? languagePack[clientTranslationSettings.value.language][key]
            : null,

        // ----------------------
        // Extend Package here...
        // ----------------------
    }
}