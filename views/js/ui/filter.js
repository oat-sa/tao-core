/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 * @author Ivan Klimchuk <klimchuk@1pt.com>
 * @author Mikhail Kamarouski <kamarouski@1pt.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/eventifier',
    'tpl!ui/filter/template',
    'ui/selecter'
], function($, _, __, eventifier, filterTpl){
    'use strict';

    var defaults = {
        placeholder : __('Filter by value'),
        width : 300,
        minimumResultsForSearch: 10,
        minimumInputLength: 3,
        enable: true,
        quietMillis: 2000,
        label: false,
    };

    /**
     * Creates a item filter component
     *
     * @param {Object[]} items - the list of items
     * @param {Object} [options] - configuration options
     * @param {String} [options.placeholder] - the filter placeholder
     * @param {Number} [options.width] - the select width
     * @param {Function} [options.formatter] - function for formatting result lines
     * @returns {itemFilter} the component
     */
    return function itemFilterFactory($container, options) {

        var selected = {uri: '', code: '', name: ''};

        options = _.defaults(options || {}, defaults);

        /**
         * The item filter component
         * @typedef {Object} itemFilter
         */
        return eventifier({

            /**
             * Render the component into a container
             * @returns {itemFilter} chains
             * @fires itemFilter#change
             */
            render: function render(format) {
                var self = this;

                var $component = $(filterTpl(options));
                var $list = $('.item-filter', $component);

                $list.select2({
                    placeholder: options.placeholder,
                    allowClear: true,
                    width: options.width,
                    minimumResultsForSearch: options.minimumResultsForSearch,
                    minimumInputLength: options.minimumInputLength,
                    maximumInputLength: 200,
                    formatResult: _.isFunction(options.formatter) ? options.formatter : function (data) {
                        return _.template(format)(data);
                    },
                    ajax: {
                        quietMillis: options.quietMillis,
                        data: function (term, page) {
                            return {
                                q: term, // search term
                                page: page
                            };
                        },
                        transport: function (params) {
                            self.trigger('request', params);
                        },
                        results: function (data, page) {
                            var more = (page * 25) < data.total;

                            return { results: data.items, more: more };
                        },
                        cache: true
                    }
                });
                $list.select2("enable", options.enable);
                $list.on('change.select2', function() {
                    selected = $list.select2('data');
                    self.trigger('change', selected && selected.uri ? selected.uri : '');
                });

                $container.append($component);

                return this;
            },

            enable: function enable() {
                $('.item-filter', $container).select2("enable", true);
            },

            disable: function disable() {
                $('.item-filter', $container).select2("enable", false);
            },

            getSelected: function getSelected() {
                return selected;
            },

            reset: function reset() {
                var $list = $('.item-filter', $container);
                $list.select2("val", "");
            },

            /**
             * Leave the place as clean as before
             * @returns {selector} for chaining
             * @fires selector#destroy
             */
            destroy: function destroy() {

                var $list = $('.item-filter', $container);
                $list.select2('destroy');
                $list.remove();

                /**
                 * The selector is destroyed
                 * @event selector#destroy
                 */
                this.trigger('destroy');

                return this;
            }
        });
    };
});
