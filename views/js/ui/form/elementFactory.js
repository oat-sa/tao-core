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
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/promise',
    'ui/component',
    'tpl!ui/form/tpl/element'
], function ($, _, Promise, component, elementTpl) {
    'use strict';

    /**
     * Factory tha builds a form element based on its definition
     *
     * @param {Object} definition
     * @param {HTMLElement|String} container
     * @returns {component}
     * @fires ready - When the component is ready to work
     */
    function elementFactory(definition, container) {
        var element;

        if (!_.isPlainObject(definition)) {
            throw new TypeError('The element must be an object');
        }
        if (!_.isString(definition.name) || !definition.name) {
            throw new TypeError('The element must have a name');
        }
        if (!_.isString(definition.type) || !definition.type) {
            throw new TypeError('The element must have a type');
        }

        // @todo
        element =  component({
            getName: function getName() {
                return definition.name;
            },
            getValue: function getValue() {
                if (this.is('rendered')) {
                    return this.getElement().find('input').val();
                }
                return '';
            },
            setValue: function setValue(value) {
                if (this.is('rendered')) {
                    this.getElement().find('input').val(value).change();
                }
                return this;
            },
            serialize: function serialize() {
                return {
                    name: this.getName(),
                    value: this.getValue()
                };
            },
            validate: function validate() {
                return Promise.resolve(true);
            },
            reset: function reset() {
                this.setValue('');
            }
        })
            .setTemplate(elementTpl)
            .on('init', function () {
                _.defer(function () {
                    element.render(container);
                });
            })
            .on('render', function () {
                this.getElement().on('change', function() {
                    element.trigger('change', element.getValue());
                });
                this.trigger('ready');
            })
            .on('disable', function () {
                this.getElement().find('input').prop('disabled', true);
            })
            .on('enable', function () {
                this.getElement().find('input').prop('disabled', false);
            });

        _.defer(function () {
            element.init(definition);
        });

        return element;
    }

    return elementFactory;
});
