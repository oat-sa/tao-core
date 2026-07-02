define(['json!i18ntr/messages.json', 'core/format'], function(i18nTr, format){
    'use strict';

    const translations = i18nTr.translations;
    const rubyTags = /\{(ruby|rt|rb|rp)\}|\{\/(ruby|rt|rb|rp)\}/g;

    /**
     * Converts ruby placeholder tags to HTML elements.
     *
     * @param {String} text
     * @returns {String}
     */
    function convertRubyTags(text) {
        return text.replace(rubyTags, (match, open, close) => {
            return open ? `<${open}>` : `</${close}>`;
        });
    }

    /**
     * Strips ruby annotations for contexts that cannot render HTML (e.g. option text).
     *
     * @param {String} text
     * @returns {String}
     */
    function plainTextFromRuby(text) {
        if (typeof text !== 'string' || text === '') {
            return text === null || text === undefined ? '' : String(text);
        }

        let plain = convertRubyTags(text);
        plain = plain.replace(/<rt[^>]*>[\s\S]*?<\/rt>/gi, '');
        plain = plain.replace(/\{rt\}[\s\S]*?\{\/rt\}/g, '');
        plain = plain.replace(/<rp[^>]*>[\s\S]*?<\/rp>/gi, '');
        plain = plain.replace(/\{rp\}[\s\S]*?\{\/rp\}/g, '');
        plain = plain.replace(/<[^>]+>/g, '');
        plain = plain.replace(/\s+/g, ' ').trim();

        return plain;
    }

    /**
     * Common translation method.
     * @see /locales/#lang#/messages_po.js
     *
     * @param {String} message should be the string in the default language (usually english) used as the key in the gettext translations
     * @returns {String} translated message
     */
    function __(message) {
        let localized = translations[message] || message;

        if (arguments.length > 1) {
            localized = format.apply(null, [localized].concat([].slice.call(arguments, 1)));
        }

        return convertRubyTags(localized);
    }

    __.plainTextFromRuby = plainTextFromRuby;

    return __;
});
