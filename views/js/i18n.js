define([
    'json!i18ntr/messages.json',
    'core/format',
    'i18n/plural',
    'i18n/ruby'
], function(i18nTr, format, pluralHelper, rubyHelper){
    'use strict';

    const translations = i18nTr && i18nTr.translations ? i18nTr.translations : {};

    /**
     * Translate a message key and format the resulting message.
     * @see /views/locales/#lang#/messages.json
     *
     * @param {String} message should be the string in the default language (usually english) used as the key in the gettext translations
     * @param {...*} args values passed to the formatter
     * @returns {String} translated message
     */
    function __(message, ...args){
        let localized = translations[message] || message;

        if (args.length > 0) {
            localized = format(localized, ...args);
        }

        return rubyHelper.convertRubyTags(localized);
    }

    /**
     * Translate a pluralized message and format the resolved variant.
     *
     * @param {String} singular
     * @param {String} plural
     * @param {Number} count
     * @param {...*} args values passed to the formatter
     * @returns {String}
     */
    __.p = function __p(singular, plural, count, ...args){
        const pluralRule = pluralHelper.getPluralRule(i18nTr.pluralForms);
        const pluralIndex = pluralRule(Number(count));
        let localized = pluralHelper.resolveTranslation(
            translations,
            singular,
            pluralIndex === 0 ? singular : plural,
            pluralIndex
        );
        const formatArgs = args.length === 0 ? [count] : args;

        localized = format(localized, ...formatArgs);

        return rubyHelper.convertRubyTags(localized);
    };

    __.plainTextFromRuby = rubyHelper.plainTextFromRuby;

    return __;
});
