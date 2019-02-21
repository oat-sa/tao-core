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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */

/**
 * This module helps you to listen for the window status.
 *
 * @example
 * pageStatusFactory()
 *   .on('show', function(){ //page shown })
 *   .on('hide', function(){ //page hidden  })
 *   .on('unload', function(){ //page unloaded })
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'lodash',
    'core/eventifier'
], function (_, eventifier) {
    'use strict';

    /**
     * The default values of the component
     */
    var defaults = {

        /**
         * What kind of status to track
         */
        track : ['load', 'visibility', 'focus']
    };

    /**
     * The visibility API properties,
     * browser dependent for the old ones.
     */
    var hiddenProp,
        visibilityChangeEvent;

    if (typeof document.hidden !== 'undefined') {
        hiddenProp = 'hidden';
        visibilityChangeEvent = 'visibilitychange';
    } else if (typeof document.mozHidden !== 'undefined') {
        hiddenProp = 'mozHidden';
        visibilityChangeEvent = 'mozvisibilitychange';
    } else if (typeof document.msHidden !== 'undefined') {
        hiddenProp = 'msHidden';
        visibilityChangeEvent = 'msvisibilitychange';
    } else if (typeof document.webkitHidden !== 'undefined') {
        hiddenProp = 'webkitHidden';
        visibilityChangeEvent = 'webkitvisibilitychange';
    }

    /**
     * Creates a pageStatus.
     *
     * @param {Object} [options] - configuration options
     * @param {Window} [options.window = window] - the target window, could be useful for popup, tabs or iframes
     * @param {String[]} [options.track] - the list of status to track in 'load', 'visibility', 'focus'
     * @returns {pageStatus} the instance, an eventifier.
     */
    return function pageStatusFactory(options){

        var win;
        var pageStatus;

        options = _.defaults(options || {}, defaults);
        win = options.window || window;

        /**
         * @type {pageStatus}
         * @fires pageStatus#statuschange when the page status changed
         */
        pageStatus = eventifier({});

        if(_.contains(options.track, 'load')){

            //the load event won't be triggered on the current window,
            //the window is already loaded
            win.addEventListener('load', function(){
                pageStatus.trigger('statuschange', 'load');
            });

            //when closing the browser
            win.addEventListener('unload', function(){
                pageStatus.trigger('statuschange', 'unload');
            });
        }

        if(_.contains(options.track, 'visibility')){

            //minimize, switch tab, move the window in background (mobile), etc.
            win.addEventListener(visibilityChangeEvent, function (e) {
                _.defer(function () {
                    if (win.document[hiddenProp] === true) {
                        pageStatus.trigger('statuschange', 'hide', e.timeStamp);
                    } else {
                        pageStatus.trigger('statuschange', 'show', e.timeStamp);
                    }
                });
            });
        }

        if(_.contains(options.track, 'focus')){

            //losing the window focus, the event can be triggered multiple time
            win.addEventListener('blur', _.debounce(function(e){
                if(e.target === win){
                    pageStatus.trigger('statuschange', 'blur', e.timeStamp);
                }
            }, 200, {leading : true, trailing: false} ));

            //losing the window focus, the event can be triggered multiple time
            win.addEventListener('focus', _.debounce(function(e){
                if(e.target === win){
                    pageStatus.trigger('statuschange', 'focus', e.timeStamp);
                }
            }, 200, {leading : true, trailing: false} ));
        }

        //trigger back sub events
        pageStatus.on('statuschange', function(status){
            var args = [status].concat([].slice.call(arguments, 1));
            pageStatus.trigger.apply(pageStatus, args);
        });

        return pageStatus;
    };
});
