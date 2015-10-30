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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'tpl!ui/component/tpl/component'
], function ($, _, defaultTpl) {
    'use strict';

    /**
     * Builds a component from a base skeleton
     * @param {Object} specs - Some extra methods to assign to the component instance
     * @param {Function} template - The DOM factory template
     * @param {Object} defaults - Some default config entries
     * @returns {component}
     */
    var component = function component(specs, template, defaults) {
        // the template is a private property
        var componentTpl;

        // base skeleton
        var instance = {
            /**
             * Initializes the component
             * @param {Object} config
             * @param {jQuery|HTMLElement|String} [config.renderTo] - An optional container in which renders the component
             * @param {Boolean} [config.replace] - When the component is appended to its container, clears the place before
             * @returns {component}
             */
            init : function init(config) {
                var initConfig = config || {};
                this.config = _.omit(initConfig, function(value) {
                    return value === undefined || value === null;
                });
                _.defaults(this.config, defaults || {});
                this.config.is = {};

                this.setup();

                if (this.config.renderTo) {
                    this.render();
                }

                return this;
            },

            /**
             * Uninstalls the component
             * @returns {component}
             */
            destroy : function destroy() {
                this.tearDown();

                if (this.$component) {
                    this.$component.remove();
                }

                this.$component = null;
                this.config.is = {};

                return this;
            },

            /**
             * Renders the component
             * @param {jQuery|HTMLElement|String} [to]
             * @returns {jQuery}
             */
            render : function render(to) {
                var renderTo = to || this.config.renderTo;
                var $to;

                this.$component = $(componentTpl(this.config));

                if (renderTo) {
                    $to = $(renderTo);
                    if (this.config.replace) {
                        $to.empty();
                    }
                    $to.append(this.$component);
                }

                this.setState('rendered', true);
                this.postRender();

                return this.$component;
            },

            /**
             * Configures the component
             */
            setup : function setup() {
                // just a template method to be overridden
            },

            /**
             * Uninstalls the component
             */
            tearDown : function tearDown() {
                // just a template method to be overridden
            },

            /**
             * Performs additional tasks on render
             */
            postRender : function postRender() {
                // just a template method to be overridden
            },

            /**
             * Shows the component
             * @returns {component}
             */
            show : function show() {
                return this.setState('hidden', false);
            },

            /**
             * Hides the component
             * @returns {component}
             */
            hide : function hide() {
                return this.setState('hidden', true);
            },

            /**
             * Enables the component
             * @returns {component}
             */
            enable : function enable() {
                return this.setState('disabled', false);
            },

            /**
             * Disables the component
             * @returns {component}
             */
            disable : function disable() {
                return this.setState('disabled', true);
            },

            /**
             * Checks if the component has a particular state
             * @param {String} state
             * @returns {Boolean}
             */
            is : function is(state) {
                return !!this.config.is[state];
            },

            /**
             * Sets the component to a particular state
             * @param {String} state
             * @param {Boolean} flag
             * @returns {component}
             */
            setState : function setState(state, flag) {
                this.config.is[state] = !!flag;

                if (this.$component) {
                    this.$component.toggleClass(state, !!flag);
                }

                return this;
            },

            /**
             * Gets the underlying DOM element
             * @returns {jQuery}
             */
            getDom : function getDom() {
                return this.$component;
            },

            /**
             * Gets the template used to render this component
             * @returns {Function}
             */
            getTemplate : function getTemplate() {
                return componentTpl;
            },

            /**
             * Sets the template used to render this component
             * @param {Function} template
             * @returns {instance}
             */
            setTemplate : function setTemplate(template) {
                var tpl = template || defaultTpl;
                componentTpl = tpl;

                // ensure the template is defined as a function
                if (!_.isFunction(componentTpl)) {
                    componentTpl = function() {
                        return tpl;
                    }
                }

                return this;
            }
        };

        // let's extend the instance with extra methods
        if (specs) {
            _.assign(instance, specs);
        }

        return instance.setTemplate(template);
    };

    return component;
});
