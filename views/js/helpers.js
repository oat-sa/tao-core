/*
 * Helpers
 *
 * @deprecated Do not use it anymore. Only here for backward compat.
 */
define([
    'lodash',
    'jquery',
    'context',
    'layout/loading-bar'
], function (_, $, context, loadingBar) {
    'use strict';

    var Helpers = {
        init: function () {
            /**
             * Extends the JQuery post method for convenience use with Json
             * @param {String} url
             * @param {Object} data
             * @param {Function} callback
             */
            $.postJson = function (url, data, callback) {
                $.post(url, data, callback, "json");
            };
        },

        getMainContainer: function () {
            console.warn('deprecated, use section instead');
            var sectionId,
                sectionIndex;
            if (!context.section) {
                sectionIndex = $('.section-container').tabs('options', 'selected');
                $('.content-panel').eq(sectionIndex).find('.content-block');
            }
            return $('#panel-' + context.section + ' .content-block');
        },

        /**
         * @return {String} the current main container jQuery selector (from the opened tab)
         */
        getMainContainerSelector: function ($tabs) {
            console.warn('deprecated, use section instead');
            var $container = this.getMainContainer();
            if ($container && $container.length > 0) {
                return $container.selector;
            }
            return false;
        },

        /*
         * Navigation and ajax helpers
         */

        /**
         * Begin an async request, while loading:
         * - show the loader img
         * - disable the submit buttons
         */
        loading: function () {
            console.warn('deprecated, this should be automated');
            $(window).on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                return false;
            });
            loadingBar.start();
        },

        /**
         * Complete an async request, once loaded:
         *  - hide the loader img
         *  - enable back the submit buttons
         */
        loaded: function () {
            console.warn('deprecated, this should be automated');
            $(window).off('click');
            loadingBar.stop();
        },

        /**
         * Load url asyncly into selector container
         * @param {String} selector
         * @param {String} url
         */
        _load: function (selector, url, data) {

            url = url || '';

            if (data) {
                data.nc = new Date().getTime();
            }
            else {
                data = {nc: new Date().getTime()};
            }
            $(selector).hide().empty().show();
            if (url.indexOf('?') === -1) {
                $(selector).load(url, data);
            }
            else {
                url += '&' + ($.param(data));
                $(selector).load(url);
            }
        },

        /*
         * others
         */

        /**
         * simple _url implementation, requires layout_header to set some global variables
         * @deprecated use util/url#route instead
         */
        _url: function (action, controller, extension, params) {

            var url;

            if(typeof action !== 'string' || typeof controller !== 'string' || typeof extension !== 'string'){
                throw new TypeError('All parts are required to build an URL');
            }

            url = context.root_url + extension + '/' + controller + '/' + action;

            if(_.isString(params)) {
                url += '?' + params;
            } else if (_.isPlainObject(params)) {
                url += '?' + $.param(params);
            }
            return url;
        }
    };

    return Helpers;
});
