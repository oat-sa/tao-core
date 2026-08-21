define(['i18ntr/messages', 'core/format'], function(i18nTr, format){
    'use strict';

    var translations = i18nTr.translations;
    var pluralRule = typeof i18nTr.p11nRules === 'function'
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

    function resolveTranslation(message, pluralFallback, pluralIndex) {
        var localized = translations[message];

        if (localized && typeof localized === 'object' && Array.isArray(localized._translations)) {
            var safeIndex = clampPluralIndex(pluralIndex, localized._translations.length);

            return localized._translations[safeIndex] || localized._translations[0] || pluralFallback || message;
        }

        return localized || pluralFallback || message;
    }

    /**
     * Common translation method.
     * @see /views/locales/#lang#/messages.js
     *
     * @param {String} message should be the string in the default language (usually english) used as the key in the gettext translations
     * @returns {String} translated message
     */
    function __(message){
        var localized = resolveTranslation(message, message, 0);

        if(arguments.length > 1){
            localized = format.apply(null, [localized].concat([].slice.call(arguments, 1)));
        }

        return localized;
    }

    __.p = function __p(singular, plural, count){
        var pluralIndex = pluralRule(Number(count));
        var localized = resolveTranslation(singular, pluralIndex === 0 ? singular : plural, pluralIndex);
        var formatArgs = [].slice.call(arguments, 3);

        if (formatArgs.length === 0) {
            formatArgs = [count];
        }

        if(formatArgs.length > 0){
            localized = format.apply(null, [localized].concat(formatArgs));
        }

        return localized;
    };

    return __;
});
