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
    'core/eventifier',
    'tpl!ui/component/tpl/component'
], function ($, _, eventifier, defaultTpl) {
    'use strict';

    var _slice = [].slice;

    /**
     * Builds a component from a base skeleton
     * @param {Object} [specs] - Some extra methods to assign to the component instance
     * @param {Object} [defaults] - Some default config entries
     * @returns {component}
     */
    var component = function component(specs, defaults) {
        // the template is a private property
        var componentTpl = defaultTpl;

        var componentState;

        // base skeleton
        var componentApi = {
            /**
             * Initializes the component
             * @param {Object} config
             * @param {jQuery|HTMLElement|String} [config.renderTo] - An optional container in which renders the component
             * @param {Boolean} [config.replace] - When the component is appended to its container, clears the place before
             * @returns {component}
             */
            init : function init(config) {
                var self = this;
                var initConfig = config || {};
                this.config = _.omit(initConfig, function(value, name) {
                    var omit = value === undefined || value === null;
                    if (!omit && !name.indexOf('on')) {
                        self.on(name.substr(2), value);
                        omit = true;
                    }
                    return omit;
                });
                _.defaults(this.config, defaults || {});
                componentState = {};

                /**
                 * Executes extra init tasks
                 * @event component#init
                 * @param {component} component
                 */
                this.trigger('init', this);

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
                /**
                 * Executes extra destroy tasks
                 * @event component#destroy
                 * @param {component} component
                 */
                this.trigger('destroy', this);

                if (this.$component) {
                    this.$component.remove();
                }

                this.$component = null;
                componentState = {};

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

                /**
                 * Executes extra render tasks
                 * @event component#render
                 * @param {jQuery} $component
                 * @param {component} component
                 */
                this.trigger('render', this.$component, this);

                return this.$component;
            },

            /**
             * Shows the component
             * @returns {component}
             */
            show : function show() {
                /**
                 * Executes extra tasks on show
                 * @event component#show
                 * @param {component} component
                 */
                this.trigger('show', this);

                return this.setState('hidden', false);
            },

            /**
             * Hides the component
             * @returns {component}
             */
            hide : function hide() {
                /**
                 * Executes extra tasks on hide
                 * @event component#hide
                 * @param {component} component
                 */
                this.trigger('hide', this);

                return this.setState('hidden', true);
            },

            /**
             * Enables the component
             * @returns {component}
             */
            enable : function enable() {
                /**
                 * Executes extra tasks on enable
                 * @event component#enable
                 * @param {component} component
                 */
                this.trigger('enable', this);

                return this.setState('disabled', false);
            },

            /**
             * Disables the component
             * @returns {component}
             */
            disable : function disable() {
                /**
                 * Executes extra tasks on disable
                 * @event component#disable
                 * @param {component} component
                 */
                this.trigger('disable', this);

                return this.setState('disabled', true);
            },

            /**
             * Checks if the component has a particular state
             * @param {String} state
             * @returns {Boolean}
             */
            is : function is(state) {
                return !!componentState[state];
            },

            /**
             * Sets the component to a particular state
             * @param {String} state
             * @param {Boolean} flag
             * @returns {component}
             */
            setState : function setState(state, flag) {
                flag = !!flag;
                componentState[state] = flag;

                if (this.$component) {
                    this.$component.toggleClass(state, flag);
                }

                /**
                 * Executes extra tasks on state change
                 * @event component#state
                 * @param {String} state
                 * @param {Boolean} flag
                 * @param {component} component
                 */
                this.trigger('state', state, flag, this);

                return this;
            },

            /**
             * Gets the underlying DOM element
             * @returns {jQuery}
             */
            getContainer : function getContainer() {
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
             * @returns {componentApi}
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

                /**
                 * Executes extra tasks on template change
                 * @event component#template
                 * @param {function} componentTpl
                 * @param {component} component
                 */
                this.trigger('template', componentTpl, this);

                return this;
            }
        };

        // let's extend the instance with extra methods
        if (specs) {
            _(specs).functions().forEach(function(method){
                componentApi[method] = function delegate(){
                    return specs[method].apply(componentApi, _slice.call(arguments));
                };
            });
        }

        eventifier(componentApi);

        return componentApi;
    };

    return component;
});
