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
    'tpl!ui/generis/widget/comboBox/comboBox'
], function (
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
     * @param {String} [config.value]
     * @returns {ui/component}
     */
    function factory(options, config) {
        var validator = options.validator || [];
        var widget;

        widget = widgetFactory({
            validator: validator
        }, {
            // no overrides
        })
        .setTemplate(tpl)
        .init({
            label: config.label,
            range: config.range || [],
            required: config.required || false,
            uri: config.uri,
            value: config.value || ''
        });

        // Validations
        if (widget.config.required) {
            widget.validator
            .addValidation({
                message: __('This field is required'),
                predicate: /\S+/,
                precedence: 1
            });
        }

        return widget;
    }

    return factory;
});
