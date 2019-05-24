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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * Defines a comboBox widget
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'tpl!ui/form/widget/tpl/comboBox'
], function ($, _, __, comboBoxTpl) {
    'use strict';

    /**
     * Makes sure a value is an array
     * @param {*} value
     * @returns {Array}
     */
    function forceArray(value) {
        if (value && !_.isArray(value)) {
            value = [value];
        } else {
            value = value || [];
        }
        return value;
    }

    /**
     * Defines the provider for a comboBox widget.
     *
     * @example
     * widgetFactory.registerProvider('comboBox', comboBoxProvider);
     */
    return {
        /**
         * Initialize the widget.
         * @param {widgetConfig} config
         */
        init: function init(config) {
            // the type will be reflected to the HTML markup
            config.widgetType = 'combo-box';

            // the value must be an array
            config.range = forceArray(config.range);

            // initial value
            this.on('render', function() {
                this.getWidgetElement().val(this.getConfig().value);
            });
        },

        /**
         * Expose the template to the factory and it will apply it
         */
        template: comboBoxTpl
    };
});
