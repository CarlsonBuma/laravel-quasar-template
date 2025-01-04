"use strict";
import { ref } from 'vue';
import { Cookies, Dark } from 'quasar'
import dateFormat from './formats/date.js';
import languagePack from './lang/index.js';

// Set Cookie 'client_
// Cookie Consent: Required by system
const setCookie = (name, value) => {
    Cookies.set(name, value, {
        secure: true,
        expires: '160'
    })

    return Cookies.get(name) ?? value
}

export default () => {

    // Client provides translation options
    const clientTranslationOptions = {
        'date': ['international', 'eu', 'us'] ,
        'lang': ['de', 'en']
    };

    // User can choose translation settings
    // We store settings as client cookies
    const clientTranslationPreferences = ref({
        dateFormat: Cookies.get('client_dateformat') 
            ?? setCookie('client_dateformat', 'international'),        
        language: Cookies.get('client_language') 
            ?? setCookie('client_language', 'en'), 
        darkmode: (Cookies.get('client_darkmode') === 'true') 
            || setCookie('client_darkmode', 'false') === 'true'
    });

    // Set environment
    Dark.set(clientTranslationPreferences.value.darkmode);

    // Return Translation Package
    return {

        // Set variables
        'set_cookie': (name, value) => setCookie(name, value),
        'set_darkmode': (value) => {
            clientTranslationPreferences.value.darkmode = value;
            Dark.set(value);
            setCookie('client_darkmode', value)
        },

        // Options
        'client_options': clientTranslationOptions,
        'client_preferences': clientTranslationPreferences,
        
        // Formatting
        'date': (rawDate) => dateFormat[clientTranslationPreferences.value.dateFormat]
            ? dateFormat[clientTranslationPreferences.value.dateFormat](rawDate) 
            : 'undefined',
        'lang': (key) => languagePack[clientTranslationPreferences.value.language] && languagePack[clientTranslationPreferences.value.language][key]
            ? languagePack[clientTranslationPreferences.value.language][key]
            : null,

        // ----------------------
        // Extend Package here...
        // ----------------------
    }
}