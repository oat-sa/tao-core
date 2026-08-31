define(function() {
    'use strict';

    const rubyTags = /\{(ruby|rt|rb|rp)\}|\{\/(ruby|rt|rb|rp)\}/g;

    /**
     * Convert ruby placeholder markers from translations into HTML ruby tags.
     *
     * @param {string} text
     * @returns {string}
     */
    function convertRubyTags(text) {
        return text.replace(rubyTags, function(match, open, close) {
            return open ? `<${open}>` : `</${close}>`;
        });
    }

    /**
     * Strip ruby annotations for contexts that need plain text only.
     *
     * @param {*} text
     * @returns {string}
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

    return {
        convertRubyTags: convertRubyTags,
        plainTextFromRuby: plainTextFromRuby
    };
});
