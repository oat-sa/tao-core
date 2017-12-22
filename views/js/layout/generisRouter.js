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
/**
 * The purpose of this router is to allow navigation between Generis views entities (sections, tree items...).
 * It does not dispatch any controller (that's the backoffice.js' job) but ensures that the browser history has
 * a consistent state and URLs. On history move, it triggers event listened by the section manager and the tree.
 * Those module actually do the job of restoring the route state.
 **
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/eventifier',
    'util/url'
], function(
    $,
    _,
    eventifier,
    urlUtil
) {
    'use strict';

    /**
     * Keep track of the latest known state
     */
    var topState;

    /**
     * The router instance
     */
    var generisRouter = eventifier({
        /**
         * To be called on section initial loading or section change.
         * This method create a new history state or replace the current one. It might be called as a convenient way
         * to add the sectionId to the current browser Url. In that case, history.replaceState() will be used.
         * Otherwise, history.pushState().
         *
         * @param {String} baseUrl - a base on which to build the stateUrl. Most of the time, it is the current URL from the call point.
         * @param {String} sectionId - to be saved in the state and added to the Url
         * @param {('activate'|'show')} restoreWith - the method needed to restore the section
         */
        pushSectionState: function pushSectionState(baseUrl, sectionId, restoreWith) {
            var parsedUrl = urlUtil.parse(baseUrl);
            var currentQuery = _.mapValues(parsedUrl.query, function(value, key) {
                return (key === 'uri') ? decodeURIComponent(value) : value;
            });
            var newQuery = _.clone(currentQuery);
            var baseUrlHasSection = currentQuery.section;

            var stateUrl;
            var newState = {
                sectionId: sectionId,
                restoreWith : restoreWith || 'activate',
                nodeUri: currentQuery.uri
            };

            if (!baseUrlHasSection) {
                // adding missing section parameter
                newQuery.section = sectionId;

            } else if (sectionId !== currentQuery.section) {
                // changing section, we need to remove any uri
                newQuery.section = sectionId;
                delete newQuery.uri;
                delete newState.nodeUri;
            }

            if (sectionId && !_.isEqual(currentQuery, newQuery)) {
                stateUrl = urlUtil.build(parsedUrl.path, newQuery);

                if (baseUrlHasSection) {
                    window.history.pushState(newState, null, stateUrl);
                    this.trigger('pushsectionstate', stateUrl);

                } else {
                    window.history.replaceState(newState, null, stateUrl);
                    this.trigger('replacesectionstate', stateUrl);
                }
                topState = newState;
            }
        },

        /**
         * To be called on node selection in the tree.
         * This method create a new history state or replace the current one. It might be called as a convenient way
         * to add the Uri parameter to the current browser Url. In that case, history.replaceState() will be used.
         * Otherwise, history.pushState().
         *
         * @param {String} baseUrl - a base on which to build the stateUrl. Most of the time, it is the current URL from the call point.
         * @param {String} nodeUri - to be saved in the state and added to the Url. Should be given as a plain non-encoded URI (ex: http://tao/mytao.rdf#i151378052813779)
         */
        pushNodeState: function pushNodeState(baseUrl, nodeUri) {
            var parsedUrl = urlUtil.parse(baseUrl);
            var currentQuery = _.mapValues(parsedUrl.query, function(value, key) {
                return (key === 'uri') ? decodeURIComponent(value) : value;
            });
            var newQuery = _.clone(currentQuery);
            var baseUrlHasUri = currentQuery.uri;

            var currentState = window.history.state || {};
            var newState = {
                sectionId: currentState.sectionId || currentQuery.section || '',
                restoreWith : currentState.restoreWith || 'activate',
                nodeUri: nodeUri
            };
            var stateUrl;

            if (nodeUri !== currentQuery.uri) {
                newQuery.uri = nodeUri;
            }

            if (nodeUri && !_.isEqual(currentQuery, newQuery)) {
                stateUrl = urlUtil.build(parsedUrl.path, newQuery);

                if (baseUrlHasUri) {
                    window.history.pushState(newState, null, stateUrl);
                    this.trigger('pushnodestate', stateUrl);

                } else {
                    window.history.replaceState(newState, null, stateUrl);
                    this.trigger('replacenodestate', stateUrl);
                }
                topState = newState;
            }
        },

        /**
         * Restore a state from the history, by triggering events relevant to the retrieved state.
         * @param {Boolean} fromPopState - if this method has been called following a popState event
         */
        restoreState: function restoreState(fromPopState) {
            var state = window.history.state || {};
            if(this.hasRestorableState()){
                // generisRouter has already been used
                if (fromPopState) {
                    topState = topState || {};

                    // changing section
                    if (topState.sectionId !== state.sectionId) {
                        this.trigger('section' + state.restoreWith, state.sectionId);

                    // changing uri
                    } else if (state.nodeUri) {
                        this.trigger('urichange', state.nodeUri, state.sectionId);
                    }

                // we are restoring in section initialisation: we only need to deal with the section,
                // as uri will be read and set during tree initialisation
                } else {
                    this.trigger('section' + state.restoreWith, state.sectionId);
                }
                topState = state;
            }
        },

        /**
         * Check that the current state contains the minimum information to restore a state
         */
        hasRestorableState: function hasRestorableState() {
            var state = window.history.state;
            return state && state.restoreWith && state.sectionId;
        },

        /**
         * Add the listener that triggers the actual routing events
         */
        init: function init() {
            $(window).on('popstate.generisRouter', function () {
                generisRouter.restoreState(true);
            });
        },

        /**
         * Removes the popstate listener
         */
        destroy: function destroy() {
            $(window).off('.generisRouter');
        }
    });

    return generisRouter;
});