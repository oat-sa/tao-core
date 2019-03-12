/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Anton Tsymuk <anton@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'tpl!ui/pageSizeSelector/tpl/pageSizeSelector',
    'select2',
], function ($, _, __, component, pageSizeSelectorTpl) {
    'use strict';

    /**
     * Default config values
     * @type {Object}
     */
    var defaults = {
        selectedSize: 25,
        options: [
            { label: '25 ' + __('items per page'), value: 25 },
            { label: '50 ' + __('items per page'), value: 50 },
            { label: '75 ' + __('items per page'), value: 75 },
            { label: '100 ' + __('items per page'), value: 100 },
            { label: '200 ' + __('items per page'), value: 200 },
        ]
    };

    /**
     * Builds a select component with page size options
     *
     * @param {Object} config
     * @param {Number} [config.selectedSize] - selected page size
     * @param {Object} [config.items] - available options
     * @returns {pageSizeSelector}
     */
    function pageSizeSelectorFactory(config) {
        var pageSizeSelectorSpecs = {
            setSelectedOption: function  setSelectedOption() {
                var options = this.config.options;
                var selectedSize = this.config.selectedSize;

                options.forEach(function (option) {
                    option.selected = option.value == selectedSize;
                });
            },
        };

        return component(pageSizeSelectorSpecs, defaults)
            .setTemplate(pageSizeSelectorTpl)
            .on('init', function () {
                this.setSelectedOption();
            })
            .on('render', function () {
                var self = this;
                var $component = this.getElement();

                $('.select2', $component).select2({
                    dropdownCssClass: 'page-size-dropdown',
                    minimumResultsForSearch: Infinity,
                }).on('change', function(e) {
                    self.trigger('change', e.val);
                });
            })
            .on('destroy', function(){
                var $component = this.getElement();

                $('.select2', $component).select2('destroy');
            })
            .init(config);
    }

    return pageSizeSelectorFactory;
});
