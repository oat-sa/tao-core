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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 'lodash', 'i18n', 'context', 'helpers', 'router', 'ui', 'core/history', 'ui/feedback', 'layout/logout-event'
], function ($, _, __, context, helpers, router, ui, history, feedback, logoutEvent) {
    'use strict';


    /**
     * The backoffice controller.
     * Starts the ajax based router, the automated error reporting and the UI listeners.
     */
    return {

        /**
         * Controller entry point
         */
        start: function start(){

                var $doc = $(document);
                var $container = $('body > .content-wrap');

                //fix backspace going back into the history
                history.fixBrokenBrowsers();

                //contextual loading, do a dispatch each time an ajax request loads an HTML page
                $doc.ajaxComplete(function(event, request, settings){
                    if(_.contains(settings.dataTypes, 'html')){

                       var urls = [settings.url];
                       var forward = request.getResponseHeader('X-Tao-Forward');
                       if(forward){
                           urls.push(forward);
                       }

                       router.dispatch(urls, function(){
                           ui.startDomComponent($container);
                       });
                    }
                });

                //dispatch also the current page (or the forward)
                router.dispatch(helpers._url(context.action, context.module, context.extension));

                //intercept errors
                //TODO this should belongs to the Router
                $doc.ajaxError(function (event, request, settings, exception) {

                    var errorMessage = __('Unknown Error');

                    if (request.status === 404 && settings.type === 'HEAD') {
                        //consider it as a "test" to check if resource exists
                        return;

                    } else if (request.status === 404 || request.status === 500) {
                        try {
                            // is it a common_AjaxResponse? Let's "duck type"
                            var ajaxResponse = $.parseJSON(request.responseText);
                            if (ajaxResponse !== null &&
                                typeof ajaxResponse.success !== 'undefined' &&
                                typeof ajaxResponse.type !== 'undefined' &&
                                typeof ajaxResponse.message !== 'undefined' &&
                                typeof ajaxResponse.data !== 'undefined') {

                                errorMessage = request.status + ': ' + ajaxResponse.message;
                            }
                            else {
                                errorMessage = request.status + ': ' + request.responseText;
                            }

                        }
                        catch (exception) {
                            // It does not seem to be valid JSON.
                            errorMessage = request.status + ': ' + request.responseText;
                        }
                    }

                    if (request.status === 403) {
                        logoutEvent();
                    } else {
                        feedback().error(errorMessage);
                    }
                });

                //initialize new components
                ui.startEventComponents($container);
        }
    };
});
