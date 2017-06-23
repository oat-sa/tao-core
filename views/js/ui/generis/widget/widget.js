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
    'ui/component',
    'tpl!tao/ui/generis/widget/widget',
    'css!tao/ui/generis/widget/widget'
], function(
    $,
    _,
    __,
    componentFactory,
    tpl
) {
    'use strict';

    /**
     * The factory
     * @param {ui/generis/validator} [options.validator]
     * @returns {ui/component}
     */
    function factory(options) {
        var widget;

        options = options || {};

        widget = componentFactory({
            /**
             * Gets widget value
             * @returns {String}
             */
            get: function () {
                var ret = this.config.value || '';

                if (this.is('rendered')) {
                    ret = this.getElement()
                    .find('[name="' + this.config.uri + '"]')
                    .val();
                }

                return ret;
            },

            /**
             * Sets widget value
             * @returns {String}
             */
            set: function (value) {
                this.config.value = value;

                if (this.is('rendered')) {
                    this.getElement()
                    .find('[name=' + this.config.uri + ']')
                    .val(value);
                }

                return this.config.value;
            },

            /**
             * Validates widget
             * @returns {this}
             */
            validate: function validate() {
                this.validator.run(this.get());
                this.validator.display();
                return this;
            },

            /**
             * Serializes widget into a name/value object for form submission
             * @returns {Object}
             */
            serialize: function serialize() {
                return {
                    name: this.config.uri,
                    value: this.get()
                };
            }
        }, {
            hidden: false,
            validator: null
        })
        .setTemplate(tpl);

        widget.validator = options.validator || null;

        return widget;
    }

    return factory;
});