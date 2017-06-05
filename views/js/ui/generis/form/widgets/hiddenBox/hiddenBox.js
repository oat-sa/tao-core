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
    'ui/generis/form/widgets/_widget',
    'tpl!ui/generis/form/widgets/hiddenBox/hiddenBox',
    'css!tao/ui/generis/form/widgets/_widget'
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
     * @returns {ui/component}
     */
    function factory() {
        return widgetFactory()
        .setTemplate(tpl)
        .on('init', function () {
            // Initialization
            this.config.confirmation = this.config.value;

            // Override get function
            this.get = function (callback) {
                var ret = [this.config.value || '', this.config.confirmation || ''];

                if (this.is('rendered')) {
                    ret = [
                        this.getElement()
                        .find('[name="' + this.config.uri + '"]')
                        .val(),
                        this.getElement()
                        .find('[name="' + this.config.uri + '_confirmation"]')
                        .val()
                    ];
                }

                if (typeof callback === 'function') {
                    callback.apply(this, [ret]);
                    return this;
                }

                return ret;
            };

            this.validations.push({
                predicate: function (values) {
                    return values[0] === values[1];
                },
                message: 'Passwords must match'
            });
        })
        .on('render', function () {
            var $el = this.getElement();

            // Override required validation
            if (this.config.required) {
                this.validations.shift();

                if (this.config.value) {
                    $el.find('label > abbr').remove();
                } else {
                    this.validations.unshift({
                        predicate: function (values) {
                            return /\S+/.test(values[0]);
                        },
                        message: 'This field is required'
                    });
                }
            }
        });
    }

    return factory;
});