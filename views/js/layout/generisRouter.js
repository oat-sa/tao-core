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
 * It does not dispatch any controller (that's the backoffice.js' job) but coordinates various modules
 * (like the sections object or the tree) to set the correct view according to the window.history state content
 *
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

    var generisRouter;

    function generisRouterFactory() {
        var sectionParamExp = /&section=([^&]*)/;
        var location = window.history.location || window.location;

        if (generisRouter) {
            return generisRouter;
        }


        /**
         * Ensures the state has an identifier and has the right format.
         * @param {Object} state The state to identify
         * @returns {Object} Returns the provided state
         */
        function setStateId(state) {
            var sectionPart, data;

            if (!state || !_.isObject(state)) {
                state = {};
            }

            if (!state.url) {
                state.url = location.href;
            }

            if (!state.id) {
                sectionPart = state.url.match(sectionParamExp);
                state.id = sectionPart && sectionPart[1];
            }

            if (!state.data) {
                state.data = {};
            }
            data = state.data;
            data.sectionId = data.sectionId || state.sectionId || state.id;
            data.restoreWith = data.restoreWith || state.restoreWith || 'activate';

            return state;
        }

        generisRouter = eventifier({
            /**
             * Add a new state to the history
             * @param {String} url - state url to save in the state. Might be modified
             * @param {Object} section
             * @param {String} [restoreWith = 'activate']
             */
            pushState: function pushState(url, section, restoreWith) {
                var parsedUrl = urlUtil.parse(url);
                var query = parsedUrl.query;
                var hasNoSection = !query.section;

                var state = {
                    sectionId: section.id,
                    restoreWith : restoreWith || 'activate'
                };
                var stateName = section.name || '';
                var stateUrl;

                if (section) {
                    query.section = section.id;
                    stateUrl = urlUtil.build(parsedUrl.path, query);

                    if (hasNoSection) {
                        window.history.replaceState(state, stateName, stateUrl);
                        this.trigger('replacestate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG replacing state with url', stateUrl);
                    } else {
                        window.history.pushState(state, stateName, stateUrl);
                        this.trigger('pushstate', stateUrl);

                        console.log('GGGGGGGGGGGGGGGGGGGG pushing state with url', stateUrl);
                    }

                    //fixme: This is a problem. We are actually triggering the show/activate action from the pushState function!!!
                    this.restoreState(this.getState());
                }
            },
            /**
             * Restore a state from the history.
             * It calls activate or show on the section saved into the state.
             * @param {Object} state - a state that has been pushed previously
             * @returns {Boolean|SectionApi} false if there is nothing to restore
             */
            restoreState: function restoreState(state) {
                if(state && state.data && state.data.sectionId){
                    this.trigger('section' + state.data.restoreWith, state.data.sectionId);
                    return true; // for backward compat
                }
            },
            /**
             * Gets the current history state.
             *
             * @returns {Object}
             */
            getState: function getState() {
                var state = window.history.state;
                return setStateId(state);
            }
        });

        //back & forward button, and push state
        $(window).on('popstate', function () {
            console.log('GGGGGGGGGGGGGGGGG poping state in generisRouter, retrieving state:');
            console.log(generisRouter.getState());
            generisRouter.restoreState(generisRouter.getState());
        });

        return generisRouter;
    }

    return generisRouterFactory;
});