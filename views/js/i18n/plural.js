define(function() {
    'use strict';

    // Keep the runtime parser narrowly scoped to normal gettext headers.
    const MAX_PLURAL_FORMS_LENGTH = 256;
    const MAX_EXPRESSION_LENGTH = 128;
    const MAX_NPLURALS = 8;

    let cachedPluralForms = null;
    let cachedPluralRule = null;

    /**
     * Normalize an index to a safe integer in the valid plural range.
     *
     * @param {number} index
     * @param {number} maxIndex
     * @returns {number}
     */
    function clampIndex(index, maxIndex) {
        if (!isFinite(index)) {
            return 0;
        }

        index = Math.floor(index);

        if (index < 0) {
            return 0;
        }

        if (index > maxIndex) {
            return maxIndex;
        }

        return index;
    }

    /**
     * Fallback plural selector for English-style singular/plural bundles.
     *
     * @returns {Function}
     */
    function createDefaultPluralRule() {
        return function defaultPluralRule(n) {
            return n === 1 ? 0 : 1;
        };
    }

    /**
     * Parse a gettext-style `Plural-Forms` header into a JS selector function.
     *
     * Example:
     * `nplurals=4; plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 3;`
     *
     * This extracts the `plural=...` expression and turns it into a selector
     * function. Because the bundle still ships gettext header text, we keep
     * the accepted syntax tight and reject headers that are unexpectedly long
     * or declare an implausible number of plural slots before reaching
     * `new Function()`.
     *
     * @param {string} pluralForms
     * @returns {Function}
     */
    function createPluralRule(pluralForms) {
        let expression;
        let pluralCount;
        let maxIndex;
        let expressionMatcher;
        let pluralCountMatcher;
        let rawRule;
        let isValidPluralRule;
        let isSafeHeaderLength;
        let isSafeExpressionLength;

        // Read the two gettext header parts we care about:
        // `nplurals=4; plural=(...)`.
        pluralCountMatcher = typeof pluralForms === 'string'
            ? /nplurals\s*=\s*(\d+)/i.exec(pluralForms)
            : null;
        expressionMatcher = typeof pluralForms === 'string'
            ? /plural\s*=\s*([^;]+)/i.exec(pluralForms)
            : null;
        expression = expressionMatcher ? expressionMatcher[1].trim() : '';
        pluralCount = pluralCountMatcher ? Number(pluralCountMatcher[1]) : 0;

        // Keep obviously abnormal headers away from runtime evaluation.
        isSafeHeaderLength = typeof pluralForms === 'string'
            && pluralForms.length <= MAX_PLURAL_FORMS_LENGTH;
        isSafeExpressionLength = expression !== ''
            && expression.length <= MAX_EXPRESSION_LENGTH;

        // Accept only the tiny operator-and-number subset used by gettext
        // plural expressions, and reject comment tokens outright.
        isValidPluralRule = typeof pluralForms === 'string'
            && pluralForms.trim() !== ''
            && isSafeHeaderLength
            && pluralCountMatcher
            && pluralCount >= 1
            && pluralCount <= MAX_NPLURALS
            && expressionMatcher
            && isSafeExpressionLength
            && /^[0-9n\s():?|&=!<>%+*./-]+$/.test(expression)
            && !/\/\*|\*\/|\/\//.test(expression);

        if (isValidPluralRule) {
            maxIndex = pluralCount - 1;

            try {
                // eslint-disable-next-line no-new-func
                rawRule = new Function('n', `"use strict"; return (${expression});`);

                return function pluralRule(n) {
                    return clampIndex(Number(rawRule(n)), maxIndex);
                };
            } catch (_) {
                // return default rule
            }
        }

        return createDefaultPluralRule();
    }

    /**
     * Get a cached plural selector for the current `Plural-Forms` string.
     *
     * @param {string} pluralForms
     * @returns {Function}
     */
    function getPluralRule(pluralForms) {
        if (cachedPluralRule && cachedPluralForms === pluralForms) {
            return cachedPluralRule;
        }

        cachedPluralForms = pluralForms;
        cachedPluralRule = createPluralRule(pluralForms);

        return cachedPluralRule;
    }

    /**
     * Check whether a plural slot exists directly on the translations object.
     *
     * @param {Object|Array} translationsMap
     * @param {number} pluralIndex
     * @returns {boolean}
     */
    function hasOwnTranslationIndex(translationsMap, pluralIndex) {
        return Object.prototype.hasOwnProperty.call(translationsMap, pluralIndex);
    }

    /**
     * Resolve the concrete localized variant for a computed plural index.
     *
     * @param {Object|Array} translationsMap
     * @param {number} pluralIndex
     * @param {string} pluralFallback
     * @param {string} message
     * @returns {string}
     */
    function resolvePluralVariant(translationsMap, pluralIndex, pluralFallback, message) {
        if (Array.isArray(translationsMap)) {
            const safeIndex = clampIndex(pluralIndex, translationsMap.length - 1);

            return translationsMap[safeIndex] || translationsMap[0] || pluralFallback || message;
        }

        if (translationsMap && typeof translationsMap === 'object') {
            if (hasOwnTranslationIndex(translationsMap, pluralIndex) && translationsMap[pluralIndex] !== '') {
                return translationsMap[pluralIndex];
            }

            if (hasOwnTranslationIndex(translationsMap, 0) && translationsMap[0] !== '') {
                return translationsMap[0];
            }
        }

        return pluralFallback || message;
    }

    /**
     * Resolve a singular or plural translation from the loaded bundle.
     *
     * @param {Object} translations
     * @param {string} message
     * @param {string} pluralFallback
     * @param {number} pluralIndex
     * @returns {string}
     */
    function resolveTranslation(translations, message, pluralFallback, pluralIndex) {
        const localized = translations[message];

        if (isPluralTranslationEntry(localized)) {
            return resolvePluralVariant(localized, pluralIndex, pluralFallback, message);
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
     * Check whether a bundle entry is a structured plural translation.
     *
     * @param {*} localized
     * @returns {boolean}
     */
    function isPluralTranslationEntry(localized) {
        return localized && typeof localized === 'object';
    }

    return {
        createDefaultPluralRule: createDefaultPluralRule,
        createPluralRule: createPluralRule,
        getPluralRule: getPluralRule,
        resolvePluralVariant: resolvePluralVariant,
        resolveTranslation: resolveTranslation,
        isPluralTranslationEntry: isPluralTranslationEntry
    };
});
