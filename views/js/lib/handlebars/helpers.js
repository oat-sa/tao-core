define(['i18n', 'lodash', 'lib/dompurify/purify'], function (__, _, DOMPurify) {
    'use strict';

    __ = __ && Object.prototype.hasOwnProperty.call(__, 'default') ? __['default'] : __;
    _ = _ && Object.prototype.hasOwnProperty.call(_, 'default') ? _['default'] : _;
    DOMPurify = DOMPurify && Object.prototype.hasOwnProperty.call(DOMPurify, 'default') ? DOMPurify['default'] : DOMPurify;

    /**
     * Registers Handlebars helpers.
     * @param {Handlebars} hb - The Handlebars entry point.
     */
    function handlebarsHelpers(hb) {
        var RUBY_HTML = /<\s*(?:ruby|rt|rp|rb)\b/i;

        function renderTranslation(translated) {
            if (typeof translated === 'string' && RUBY_HTML.test(translated)) {
                return new hb.SafeString(DOMPurify.sanitize(translated));
            }

            return translated;
        }

        hb.registerHelper('__', function (context) {
            return renderTranslation(__(context));
        });

        hb.registerHelper('__p', function (...args) {
            args.pop();

            return renderTranslation(__.p(...args));
        });

        hb.registerHelper('dompurify', function (context) {
            return DOMPurify.sanitize(context);
        });

        hb.registerHelper('join', function joinHelper(arr, keyValueGlue, fragmentGlue, wrapper) {
            var fragments = [];

            keyValueGlue = typeof keyValueGlue === 'string' ? keyValueGlue : void 0;
            fragmentGlue = typeof fragmentGlue === 'string' ? fragmentGlue : ' ';
            wrapper = typeof wrapper === 'string' ? wrapper : '"';

            _.forIn(arr, function (value, key) {
                var fragment = '';

                if (value !== null || typeof value !== 'undefined') {
                    if (typeof value === 'boolean') {
                        value = value ? 'true' : 'false';
                    } else if (typeof value === 'object') {
                        value = _.values(value).join(' ');
                    }
                } else {
                    value = '';
                }

                if (typeof keyValueGlue !== 'undefined') {
                    fragment += key + keyValueGlue;
                }

                fragment += wrapper + value + wrapper;
                fragments.push(fragment);
            });

            return fragments.join(fragmentGlue);
        });

        hb.registerHelper('for', function forHelper(startIndex, stopIndex, increment, options) {
            var i;
            var ret = '';

            startIndex = parseInt(startIndex, 10);
            stopIndex = parseInt(stopIndex, 10);
            increment = parseInt(increment, 10);

            for (i = startIndex; i < stopIndex; i += increment) {
                ret += options.fn(_.extend({}, this, { i: i }));
            }

            return ret;
        });

        hb.registerHelper('equal', function equalHelper(var1, var2, options) {
            if (var1 === var2) {
                return options.fn(this);
            }

            return options.inverse(this);
        });

        hb.registerHelper('property', function (name, context) {
            if (typeof context[name] !== 'undefined') {
                return new hb.SafeString(context[name]);
            }

            return '';
        });

        hb.registerHelper('includes', function includesHelper(haystack, needle, options) {
            if (_.includes(haystack, needle)) {
                return options.fn(this);
            }
        });
    }

    return handlebarsHelpers;
});
