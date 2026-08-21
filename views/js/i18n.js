define(['i18ntr/messages', 'core/format'], function(i18nTr, format){
    'use strict';

    const translations = i18nTr && i18nTr.translations ? i18nTr.translations : {};
    const rubyTags = /\{(ruby|rt|rb|rp)\}|\{\/(ruby|rt|rb|rp)\}/g;
    const pluralRule = typeof i18nTr.p11nRules === 'function'
        ? i18nTr.p11nRules
        : createDefaultPluralRule();

    function clampPluralIndex(index, total) {
        if (!isFinite(index)) {
            return 0;
        }

        if (index < 0) {
            return 0;
        }

        if (index >= total) {
            return total - 1;
        }

        return index;
    }

    function createDefaultPluralRule() {
        return function defaultPluralRule(n) {
            return n === 1 ? 0 : 1;
        };
    }

    function hasOwnTranslationIndex(translationsMap, pluralIndex) {
        return Object.prototype.hasOwnProperty.call(translationsMap, pluralIndex);
    }

    function resolvePluralVariant(translationsMap, pluralIndex, pluralFallback, message) {
        if (Array.isArray(translationsMap)) {
            const safeIndex = clampPluralIndex(pluralIndex, translationsMap.length);

            return translationsMap[safeIndex] || translationsMap[0] || pluralFallback || message;
        }

        if (translationsMap && typeof translationsMap === 'object') {
            if (hasOwnTranslationIndex(translationsMap, pluralIndex)) {
                return translationsMap[pluralIndex];
            }

            if (hasOwnTranslationIndex(translationsMap, 0)) {
                return translationsMap[0];
            }
        }

        return pluralFallback || message;
    }

    function resolveTranslation(message, pluralFallback, pluralIndex) {
        const localized = translations[message];

        if (localized && typeof localized === 'object' && localized._translations) {
            return resolvePluralVariant(localized._translations, pluralIndex, pluralFallback, message);
        }

        if (
            typeof pluralFallback !== 'undefined'
            && pluralFallback !== message
            && (typeof localized === 'undefined' || typeof localized === 'string')
        ) {
            return pluralFallback;
        }

        return localized || message;
    }

    /**
     * Converts ruby placeholder tags to HTML elements.
     *
     * @param {String} text
     * @returns {String}
     */
    function convertRubyTags(text) {
        return text.replace(rubyTags, function(match, open, close) {
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
            return text === null || typeof text === 'undefined' ? '' : String(text);
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
     * @see /views/locales/#lang#/messages.js
     *
     * @param {String} message should be the string in the default language (usually english) used as the key in the gettext translations
     * @param {...*} args values passed to the formatter
     * @returns {String} translated message
     */
    function __(message, ...args){
        let localized = resolveTranslation(message, message, 0);

        if (args.length > 0) {
            localized = format(localized, ...args);
        }

        return convertRubyTags(localized);
    }

    __.p = function __p(singular, plural, count, ...args){
        const pluralIndex = pluralRule(Number(count));
        let localized = resolveTranslation(singular, pluralIndex === 0 ? singular : plural, pluralIndex);
        const formatArgs = args.length === 0 ? [count] : args;

        localized = format(localized, ...formatArgs);

        return convertRubyTags(localized);
    };

    __.plainTextFromRuby = plainTextFromRuby;

    return __;
});
