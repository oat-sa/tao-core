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
 * Copyright (c) 2016  (original work) Open Assessment Technologies SA ;
 */
/**
 * Plugin modelisation :
 *  - helps you to create plugin's definition
 *  - helps you to bind plugin's behavior to the host
 *  - have it's own state and lifecycle convention (install -> init -> render -> finish -> destroy)
 *  - promise based
 *
 * @example
 *
 * 1. create the plugin definition
 * var factory = pluginFactory({
 *   name : 'foo',
 *   init(){
 *      console.log('foo');
 *   }
 * });
 *
 * 2.instiantiate it
 * var plugin = factory(myPluginHost);
 *
 * 3. Link it to your host lifecycle
 * myPluginHost({
 *  init(){
 *      plugin.init();
 *  }
 * });
 *
 *
 * @author Sam <sam@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/delegator',
    'core/promise'
], function (_, delegator, Promise){
    'use strict';

    /**
     * Meta factory for plugins. Let's you create a plugin definition.
     *
     * @param {Object} provider - the plugin provider
     * @param {String} provider.name - the plugin name
     * @param {Function} provider.init - the plugin initialization method
     * @param {Function} [provider.install] - plugin installer called after the instance has been bound with its host
     * @param {Function} [provider.render] - plugin rendering behavior
     * @param {Function} [provider.finish] - plugin finish behavior
     * @param {Function} [provider.destroy] - plugin destroy behavior
     * @param {Function} [provider.show] - plugin show behavior
     * @param {Function} [provider.hide] - plugin hide behavior
     * @param {Function} [provider.enable] - plugin enable behavior
     * @param {Function} [provider.disable] - plugin disable behavior
     * @param {Object} defaults - default configuration to be assigned
     * @param {String} [defaults.hostName] - the name of the host, used to alias the getHost method to getHostName
     * @returns {Function} - the generated plugin factory
     */
    function pluginFactory(provider, defaults){
        var pluginName;

        if(!_.isPlainObject(provider) || !_.isString(provider.name) || _.isEmpty(provider.name) || !_.isFunction(provider.init)){
            throw new TypeError('A plugin should be defined at least by a name property and an init method');
        }

        pluginName = provider.name;

        defaults = defaults || {};

        /**
         * The configured plugin factory
         *
         * @param {host} host - the plugin host instance
         * @param {areaBroker} [areaBroker] - an instance of an areaBroker. This should be your access point to GUI.
         * @param {Object} [config] - plugin configuration
         * @returns {plugin} the plugin instance
         */
        return function instanciatePlugin(host, areaBroker, config){
            var plugin, delegate;

            var states = {};

            var pluginContent = {};

            //basic checking for the host
            if(!_.isObject(host) || !_.isFunction(host.on) || !_.isFunction(host.trigger)){
                throw new TypeError('A plugin host should be a valid eventified object');
            }

            config = _.defaults(config || {}, defaults);

            /**
             * The plugin instance.
             * @typedef {plugin}
             */
            plugin = {

                /**
                 * Called when the host is installing the plugins
                 * @returns {Promise} to resolve async delegation
                 */
                install : function install(){
                    var self = this;

                    return delegate('install').then(function(){
                        self.trigger('install');
                    });
                },

                /**
                 * Called when the host is initializing
                 * @param {Object|*} [content] the plugin content
                 * @returns {Promise} to resolve async delegation
                 */
                init : function init(content){
                    var self = this;
                    states = {};

                    if(content){
                        pluginContent = content;
                    }

                    return delegate('init', content).then(function(){
                        self.setState('init', true)
                            .trigger('init');
                    });
                },

                /**
                 * Called when the host is rendering
                 * @returns {Promise} to resolve async delegation
                 */
                render : function render(){
                    var self = this;

                    return delegate('render').then(function(){
                        self.setState('ready', true)
                            .trigger('render')
                            .trigger('ready');
                    });
                },

                /**
                 * Called when the host is finishing
                 * @returns {Promise} to resolve async delegation
                 */
                finish : function finish(){
                    var self = this;

                    return delegate('finish').then(function(){
                        self.setState('finish', true)
                            .trigger('finish');
                    });
                },

                /**
                 * Called when the host is destroying
                 * @returns {Promise} to resolve async delegation
                 */
                destroy : function destroy(){
                    var self = this;

                    return delegate('destroy').then(function(){

                        config = {};
                        states = {};

                        self.setState('init', false);
                        self.trigger('destroy');
                    });
                },

                /**
                 * Triggers the events on the host using the pluginName as namespace
                 * and prefixed by plugin-
                 * For example trigger('foo') will trigger('plugin-foo.pluginA') on the host
                 *
                 * @param {String} name - the event name
                 * @param {...} args - additional args are given to the event
                 * @returns {plugin} chains
                 */
                trigger : function trigger(name){
                    var args = [].slice.call(arguments, 1);
                    host.trigger.apply(host, ['plugin-' + name + '.' + pluginName, plugin].concat(args));
                    return this;
                },

                /**
                 * Get the plugin host
                 * @returns {host} the plugins's host
                 */
                getHost : function getHost(){
                    return host;
                },

                /**
                 * Get the host's areaBroker
                 * @returns {areaBroker} the areaBroker
                 */
                getAreaBroker : function getAreaBroker(){
                    return areaBroker;
                },

                /**
                 * Get the config
                 * @returns {Object} config
                 */
                getConfig : function getConfig(){
                    return config;
                },

                /**
                 * Set a config entry
                 * @param {String|Object} name - the entry name or an object to merge
                 * @param {*} [value] - the config value if name is an entry
                 * @returns {plugin} chains
                 */
                setConfig : function setConfig(name, value){
                    if(_.isPlainObject(name)){
                        config = _.defaults(name, config);
                    }else{
                        config[name] = value;
                    }
                    return this;
                },

                /**
                 * Get a state of the plugin
                 *
                 * @param {String} name - the state name
                 * @returns {Boolean} if active, false if not set
                 */
                getState : function getState(name){
                    return !!states[name];
                },

                /**
                 * Set a state to the plugin
                 *
                 * @param {String} name - the state name
                 * @param {Boolean} active - is the state active
                 * @returns {plugin} chains
                 * @throws {TypeError} if the state name is not a valid string
                 */
                setState : function setState(name, active){
                    if(!_.isString(name) || _.isEmpty(name)){
                        throw new TypeError('The state must have a name');
                    }
                    states[name] = !!active;

                    return this;
                },

                /**
                 * Get the plugin content
                 *
                 * @returns {Object|*} the content
                 */
                getContent : function getContent(){
                    return pluginContent;
                },


                /**
                 * Set the plugin content
                 *
                 * @param {Object|*} [content] - the plugin content
                 * @returns {plugin} chains
                 */
                setContent : function setContent(content){
                    pluginContent = content;

                    return this;
                },

                /**
                 * Get the plugin name
                 *
                 * @returns {String} the name
                 */
                getName : function getName(){
                    return pluginName;
                },

                /**
                 * Shows the component related to this plugin
                 * @returns {Promise} to resolve async delegation
                 */
                show : function show(){
                    var self = this;

                    return delegate('show').then(function(){
                        self.setState('visible', true)
                            .trigger('show');
                    });
                },

                /**
                 * Hides the component related to this plugin
                 * @returns {Promise} to resolve async delegation
                 */
                hide : function hide(){
                    var self = this;

                    return delegate('hide').then(function(){
                        self.setState('visible', false)
                            .trigger('hide');
                    });
                },

                /**
                 * Enables the plugin
                 * @returns {Promise} to resolve async delegation
                 */
                enable : function enable(){
                    var self = this;

                    return delegate('enable').then(function(){
                        self.setState('enabled', true)
                            .trigger('enable');
                    });
                },

                /**
                 * Disables the plugin
                 * @returns {Promise} to resolve async delegation
                 */
                disable : function disable(){
                    var self = this;

                    return delegate('disable').then(function(){
                        self.setState('enabled', false)
                            .trigger('disable');
                    });
                }
            };

            /**
             * Delegate a function call to the provider
             *
             * @param {String} fnName - the function name
             * @param {...} args - additional args are given to the provider
             * @returns {*} up to the provider
             */
            delegate = delegator(plugin, provider, {
                eventifier: false,
                wrapper: function pluginWrapper(response){
                    return Promise.resolve(response);
                }
            });

            //add a convenience method that alias getHost using the hostName
            if(_.isString(defaults.hostName) && !_.isEmpty(defaults.hostName)){
                plugin['get' + defaults.hostName.charAt(0).toUpperCase() + defaults.hostName.slice(1)] = plugin.getHost;
            }

            return plugin;
        };
    }

    return pluginFactory;
});
