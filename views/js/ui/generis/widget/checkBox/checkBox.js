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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

define([
    'jquery',
    'lodash',
    'i18n',
    'ui/generis/widget/widget',
    'tpl!ui/generis/widget/checkBox/checkBox'
], function(
    $,
    _,
    __,
    widgetFactory,
    tpl
) {
    'use strict';

    /**
     * The factory
     * @param {Object[]} [options.validator]
     * @param {String} config.label
     * @param {String[]} config.range
     * @param {String} [confgi.required = false]
     * @param {String} config.uri
     * @param {String[]} [config.values]
     * @returns {ui/component}
     */
    function factory(options, config) {
        var validator = options.validator || [];
        var widget;

        // todo - handle required fields

        widget = widgetFactory({
            validator: validator
        }, {
            get: function () {
                var ret = this.config.values || [];

                if (this.is('rendered')) {
                    ret = this.getElement()
                    .find('.option > input:checked')
                    .map(function () {
                        return $(this).val();
                    });
                }

                return ret;
            },

            set: function (values) {
                if (Array.isArray(values)) {
                    this.config.values = values;
                } else {
                    this.config.values.push(values);
                }

                if (this.is('rendered')) {
                    _.each(this.config.values, function (value) {
                        this.getElement()
                        .find('input[name=' + value + ']')
                        .prop('checked', true);
                    });
                }

                return this.config.values;
            }
        })
        .setTemplate(tpl)
        .init({
            label: config.label,
            range: config.range || [],
            required: config.required || false,
            uri: config.uri,
            values: config.values || []
        });

        return widget;
    }

    return factory;
});