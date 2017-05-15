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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'jquery',
    'core/historyRouter',
    'core/logger',
    'core/eventifier',
    'core/statifier',
    'util/url',
    'ui/feedback',
    'layout/logout-event'
], function (_, $, historyRouterFactory, loggerFactory, eventifier, statifier, urlUtil, feedback, logoutEvent) {
    'use strict';

    /**
     * Shared router that will manage the page for each controller
     * @type {historyRouter}
     */
    var historyRouter;

    /**
     *
     */
    var redirectUrl;

    /**
     * Creates a logger for the app
     * @type {logger}
     */
    var appLogger = loggerFactory('controller/app');

    /**
     * Defines an application controller that will manage the routes through the history.
     * It will start by dispatching the current location, in order to keep history consistency.
     * To properly use this application controller you need to take care of it in each controller
     * that is intended to be routed through the history. See samples below.
     *
     * @example
     *  // Defines a controller that is routable through the history
     *  return {
     *      // Will be called each time the history routes the action to this controller
     *      start: function start() {
     *          // Take care of the application controller by applying a hook on each routable links
     *          return appController.apply('.link');
     *
     *          // You can also be notified of a change in the route,
     *          // and release some resources as this controller will be destroyed.
     *          // Pay attention to the event namespace, it must be unique.
     *          appController.on('change.myController', function() {
     *              // Release the event, as this controller will be destroyed
     *              appController.off('change.myController');
     *
     *              // Release resources
     *              ...
     *          });
     *
     *          // Do the stuff of the controller
     *          ...
     *
     *          // If you need to change the current route you can rely on the router brought by the appController
     *          appController.getRouter().redirect(url);
     *  };
     *
     * @typedef {appController}
     */
    var appController = eventifier(statifier({
        /**
         * App controller entry point: set up the router.
         * @param {Object} options
         * @param {String} [options.forwardTo] - an optional route of a client controller to forward
         * @param {String} [options.redirectUrl] - an optional url to redirect client on authorisation errors
         */
        start: function start(options){
            var currentRoute;

            // all links that are tagged with the "router" class are dispatched using the history router
            appController.apply();

            // dispatch the current route
            if (options && options.forwardTo) {
                currentRoute = options.forwardTo;
            } else {
                currentRoute = window.location + '';
            }

            if (options && options.redirectUrl) {
                redirectUrl = options.redirectUrl || {};
            }
            historyRouter.forward(currentRoute);
        },

        /**
         * Catch all links below the target, when they have the provided selector,
         * then dispatch them using the history router.
         * @param {String} [selector] - The CSS signature of links to catch (default: ".router")
         * @param {String|HTMLElement|jQuery} [target] - The container from which catch links (default: document)
         * @returns {appController}
         */
        apply: function apply(selector, target) {
            selector = selector || '.router';
            target = target || document;

            $(target).off('click.appController').on('click.appController', selector, function (e) {
                var $elt, href;

                // prevent the browser to actually change the page from this link
                e.preventDefault();

                // try to get the target of the link
                $elt = $(this);
                href = $elt.attr('href');
                if (!href) {
                    href = $('[href]:first-child', $elt).attr('href');
                }

                // use the history router to change the current view
                // the called controller will have in charge to get the data and update the view accordingly
                if (href) {
                    historyRouter.redirect(href);
                }
            });

            return this;
        },

        /**
         * Exposes the router so other controllers can dispatch a route
         *
         * @returns {router} the router
         */
        getRouter: function getRouter() {
            return historyRouter;
        },

        /**
         * Exposes the logger so other controllers can log application level events
         *
         * @returns {logger} the router
         */
        getLogger: function getLogger() {
            return appLogger;
        },

        /**
         * Catches errors
         * @param {Object} err
         * @returns {appController}
         */
        onError: function onError(err) {
            var message = err && err.message || err;
            var options = {message: message};

            appLogger.error(err);
            if (err.code === 403){
                options = _.defaults(options, redirectUrl ||  {});
                logoutEvent(options);
            }else{
                feedback().error(message);
            }
            return this;
        }
    }));

    // setup the history router
    historyRouter = historyRouterFactory()
        .on('dispatching', function (url) {
            appController.setState('dispatching');
            appController.trigger('change', url);
        })
        .on('dispatched', function (url) {
            appController.setState('dispatching', false);
            appController.trigger('started', url);
        });

    return appController;
});
