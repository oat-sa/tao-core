define(['i18n', 'lodash', 'lib/dompurify/purify'], function(__, _, DOMPurify) {
    'use strict';

    return function handlebarsHelpers(hb) {
        var RUBY_HTML = /<\s*(?:ruby|rt|rp|rb)\b/i;

        function renderTranslation(translated) {
            if (typeof translated === 'string' && RUBY_HTML.test(translated)) {
                return new hb.SafeString(DOMPurify.sanitize(translated));
            }

            return translated;
        }

        hb.registerHelper('__', function(context) {
            return renderTranslation(__(context));
        });

        hb.registerHelper('__p', function() {
            var args = Array.prototype.slice.call(arguments, 0, -1);

            return renderTranslation(__.p.apply(__, args));
        });
    };
});
