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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */

/**
 * Turns a component into a window, with a title bar and a control area (close...)
 *
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'jquery',
    'ui/component/placeable',
    'tpl!ui/component/tpl/window'
], function (_, $, makePlaceable, windowTpl) {
    'use strict';

    var eventNs = '.windowed',
        cssNs = '.window-component',

        defaultConfig = {
            hasCloser: true
        };

    var windowedComponentAPI = {
        /**
         * @returns {jQuery} - the container where the title bar controls are rendered
         */
        getControls: function getControls() {
            var $component = this.getElement();
            return $component.find(cssNs + '-controls');
        },

        /**
         * @returns {jQuery} - the container where the title is rendered
         */
        getTitle: function getTitle() {
            var $component = this.getElement();
            return $component.find(cssNs + '-title');
        },

        /**
         * @returns {jQuery} - the content area of the window
         */
        getBody: function getBody() {
            var $component = this.getElement();
            return $component.find(cssNs + '-body');
        },

        /**
         * Adds a control to the control area
         * @param {String} controlOptions.id
         * @param {Number} controlOptions.order - position relative to the other controls
         * @param {String} controlOptions.icon
         * @param {String} controlOptions.description - link description on mouse over
         * @param {Function} controlOptions.onclick - what to do when the control is clicked
         * @returns {component}
         */
        addControl: function addControl(controlOptions) {
            if (!_.isString(controlOptions.id) || _.isEmpty(controlOptions.id)) {
                throw new Error('control must have an id');
            }
            if (!_.isString(controlOptions.icon) || _.isEmpty(controlOptions.icon)) {
                throw new Error('control must have an icon');
            }
            if (!_.isFunction(controlOptions.onclick)) {
                throw new Error('control must have an onclick listener');
            }
            if (!_.isArray(this._windowControls)) {
                this._windowControls = [];
            }

            this._windowControls.push(controlOptions);
            return this;
        },

        /**
         * Pre-configured control to close the component
         * @returns {component}
         */
        addCloser: function addCloser() {
            return this.addControl({
                id: 'closer',
                order: 100,
                icon: 'result-nok',
                description: 'Close',
                onclick: function onclick() {
                    this.hide();

                    /**
                     * Executes extra close tasks
                     * @event component#close
                     */
                    this.trigger('close');
                }
            });
        },

        /**
         * Render the controls buttons in the title bar
         * @returns {component}
         * @private
         */
        _renderControls: function _renderControls() {
            var self = this,
                $controlsArea = this.getControls(),
                controlsCallbacks = {};

            if (_.isArray(this._windowControls)) {
                $controlsArea.empty();

                // sort controls
                this._windowControls.sort(function sortAscending(a, b) {
                    return (a.order || 0) - (b.order || 0);
                });

                // render controls
                this._windowControls.forEach(function(control) {
                    var $control = $('<button>', {
                        'class': 'icon-' + control.icon,
                        'data-control': control.id,
                        'title': control.description
                    });
                    $controlsArea.append($control);

                    controlsCallbacks[control.id] = control.onclick;
                });

                // add behavior
                $controlsArea
                    .off('click' + eventNs)
                    .on('click' + eventNs, function(e) {
                        var controlId = $(e.target).data('control');

                        if (_.isFunction(controlsCallbacks[controlId])) {
                            controlsCallbacks[controlId].call(self);
                        }
                    });
            }
            return this;
        }
    };

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config
     * @param {Boolean} hasCloser - auto-add the closer control to the title bar
     * @param {String} windowTitle - to be rendered in the title bar
     */
    return function makeWindowed(component, config) {

        _.assign(component, windowedComponentAPI);

        if (! makePlaceable.isPlaceable(component)) {
            makePlaceable(component);
        }

        return component
            .setTemplate(windowTpl)
            .off(eventNs)
            .on('init' + eventNs, function() {
                _.defaults(this.config, config || {}, defaultConfig);

                if (this.config.hasCloser) {
                    this.addCloser();
                }
            })
            .on('render' + eventNs, function() {
                this._renderControls();
            })
            .on('destroy' + eventNs, function() {
                var $controlsArea = this.getControls();
                $controlsArea.off(eventNs);
            });
    };

});
