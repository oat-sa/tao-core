define(['json!i18ntr/messages.json', 'core/format'], function(i18nTr, format){
    'use strict';

    const translations = i18nTr.translations;
    const rubyTags = /\{(ruby|rt|rb|rp)\}|\{\/(ruby|rt|rb|rp)\}/g;

    /**
     * Common translation method.
     * @see /locales/#lang#/messages_po.js
     *
     * @param {String} message should be the string in the default language (usually english) used as the key in the gettext translations
     * @returns {String} translated message
     */
    return function __(message){
        let localized = translations[message] || message;

        if(arguments.length > 1){
            localized = format.apply(null, [localized].concat([].slice.call(arguments, 1)));
        }

        return localized.replace(rubyTags, (match, open, close) => {
            return open ? `<${open}>` : `</${close}>`;
        });
    };
});
