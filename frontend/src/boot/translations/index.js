"use strict";
import { ref } from 'vue';
import dateFormat from './formats/date.js';
import languagePack from './lang/index.js';

export default () => {

    // Client provides translation options
    const clientTranslationOptions = {
        'date': ['international', 'eu', 'us', 'test'] ,
        'lang': ['de', 'en', 'error']
    };

    // Client can choose translation settings
    const clientTranslationSettings = ref({
        dateFormat: 'international',        
        language: 'en' 
    });

    // Return Package
    return {
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