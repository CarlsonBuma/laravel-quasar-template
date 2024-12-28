"use strict";
import { ref } from 'vue';
import dateFormat from './formats/date.js';
import languagePack from './lang/index.js';

export default () => {

    // Client can choose translation settings
    const clientTranslationSettings = ref({
        dateFormat: 'international',        
        language: 'en' 
    });

    // Client can choose between translation options
    const clientTranslationOptions = {
        'date': ['international', 'eu', 'us'] ,
        'lang': ['de', 'en']
    };

    // Return Package
    return {
        'client_settings': clientTranslationSettings,
        'client_options': clientTranslationOptions,
        'date': (rawDate) => dateFormat[clientTranslationSettings.value.dateFormat]
            ? dateFormat[clientTranslationSettings.value.dateFormat](rawDate) 
            : null,
        'lang': (key) => languagePack[clientTranslationSettings.value.language] && languagePack[clientTranslationSettings.value.language][key]
            ? languagePack[clientTranslationSettings.value.language][key]
            : null

        // ----------------------
        // Extend Package here...
        // ----------------------
    }
}