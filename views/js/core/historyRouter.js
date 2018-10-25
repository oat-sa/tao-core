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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 */

/**
 * The history router is a router that dispatch based on the browser history
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/router',
    'core/eventifier',
    'core/statifier',
    'core/promise'
], function ($, _, router, eventifier, statifier, Promise) {
    'use strict';

    var historyRouter;
    var location = (window.history.location || window.location) + '';

    /**
     * Create an history router
     * @exports core/historyRouter
     *
     * @example
     * var router = historyRouter();
     * router.trigger('dispatch', url);
     *
     * @returns {historyRouter} the router (same instance)
     */
    function historyRouterFactory(){
        var pendingPromise;

        if(historyRouter){
            return historyRouter;
        }

        /**
         * @typedef historyRouter
         * @see core/eventifier
         * @see core/statifier
         */
        historyRouter =  eventifier(statifier({
            /**
             * Redirects the page to another controller. Adds a step to the history.
             * @param {String} url
             * @returns {Promise}
             */
            redirect: function redirect(url) {
                return this.pushState(url);
            },

            /**
             * Forwards to another controller. Does not change the current location, just loads the target controller.
             * Will replace the current history state by an obfuscated version that displays the current location but
             * internally routes to the provided URL.
             * @param {String} url
             * @returns {Promise}
             */
            forward: function forward(url) {
                var state = _.isString(url) ? { url : url } : url;
                window.history.replaceState(state, '', window.location + '');
                return this.dispatch(state, false);
            },

            /**
             * Forwards to another controller. Replaces the current location and replace the history.
             * @param {String} url
             * @returns {Promise}
             */
            replace: function replace(url) {
                return this.dispatch(url, true);
            },

            /**
             * Dispatch manually and replace the current state if necessary
             * @param {Object|String} state - the state object or directly the URL
             * @param {String} state.url - if the state is an object, then it must have an URL to dispatch
             * @param {Boolean} [replace = false] - if we replace the current state
             * @returns {Promise}
             *
             * @fires historyRouter#dispatching before dispatch
             * @fires historyRouter#dispatched  once dispatch succeed
             */
            dispatch : function dispatch(state, replace){
                var self = this;
                function doDispatch() {
                    if(_.isString(state)){
                        state = { url : state };
                    }
                    if(!state || !state.url){
                        return Promise.reject(new TypeError("The state should contain an URL!"));
                    }

                    /**
                      * @event historyRouter#dispatching
                      * @param {String} url
                      */
                    self.setState('dispatching')
                        .trigger('dispatching', state.url);

                    if(replace === true){
                        window.history.replaceState(state, '', state.url);
                    }

                    return router
                        .dispatch(state.url)
                        .then(function(){

                            /**
                             * @event historyRouter#dispatched
                             * @param {String} url
                             */
                            self.trigger('dispatched', state.url)
                                .setState('dispatching', false);

                            return state.url;
                        });
                }

                if (pendingPromise) {
                    pendingPromise = pendingPromise.then(doDispatch).catch(doDispatch);
                } else {
                    pendingPromise = doDispatch();
                }
                return pendingPromise;
            },

            /**
             * Push a new state.
             * You can either call pushState or trigger the 'dispatch' event.
             * @param {Object|String} state - the state object or directly the URL
             * @param {String} state.url - if the state is an object, then it must have an URL to dispatch
             * @returns {Promise}
             */
            pushState : function pushState(state){
                if(_.isString(state)){
                    state = { url : state };
                }
                window.history.pushState(state, '', state.url);
                return this.dispatch(state);
            }
        }));

        // ensure the current route is in the history
        window.history.replaceState({url: location}, '', location);

        //back & forward button, and push state
        $(window).on('popstate', function () {
            historyRouter.dispatch(window.history.state);
        });

        //listen for dispatch event in order to push a state
        historyRouter.on('dispatch', function (state) {
            if(state){
                this.pushState(state);
            }
        });

        return historyRouter;
    }

    return historyRouterFactory;
});
