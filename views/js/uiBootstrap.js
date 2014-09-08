/**
 * UiBootstrap class enable you to run the naviguation mode,
 * bind the events on the main components and initialize handlers
 *
 *
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require [helpers.js]
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @author Jehan Bihin (using class.js)
 */

define([
    'jquery',
    'i18n',
    'context',
    'helpers',
    'ui/feedback',
    'layout/actions',
    'layout/tree',
    'jqueryui'],

    function ($, __, context, helpers, feedback, actions, treeFactory) {

    var UiBootstrap = {

        init: function (options) {

            //TODO move tabs to layout/section or layout/tabs

            var self = this;
            var $tabs = $('.section-container');
            var $panel = $('.content-panel');
                $panel.prop('require-refresh', !!$panel.data('refresh'));
            
            this.initAjax();
            this.initNav();

            //create tabs
            this.tabs = $tabs.tabs({
                show: function (e, ui) {
                    var $section = $(ui.panel);

                   // $('.navigation-panel').hide();

                    context.section = $section.attr('id');
 
                    $('.taotree', $section).each(function(){
                        var $treeElt = $(this),
                            $actionBar = $('.tree-action-bar-box', $section);
                        
                        treeFactory($treeElt, $treeElt.data('url'), {
                            actions : {
                                'selectClass'    : $treeElt.data('action-selectclass'),
                                'selectInstance' : $treeElt.data('action-selectinstance'),
                                'moveInstance'   : $treeElt.data('action-moveinstance'),
                                'delete'         : $treeElt.data('action-delete')
                            }
                        });
                        $treeElt.on('ready.taotree', function() {
                            $actionBar.addClass('active');
                        });
                    });
                    actions.init();

                    // navBar.init() replace
                }
            });

            //Enable the closing tab if added after the init
            this.tabs.tabs("option", "tabTemplate", '<li class="closable"><a href="#{href}"><span>#{label}</span></a><span class="tab-closer" title="' + __('Close tab') + '">x</span></li>');
            this.tabs.on("tabsadd", function (event, ui) {
                //Close the new content div
                $(ui.panel).addClass('ui-tabs-hide');
            });
            //Closer tab icon
            $(document).on('click', '.tab-closer', function (e) {
                e.preventDefault();
                self.tabs.tabs('remove', $(this).parent().index());
                //Select another by default ?
                self.tabs.tabs('select', 0);
            });

            var $tabContainer = $('.tab-container');
            if($tabContainer.find('li').length < 2) {
                $tabContainer.hide();
            }
        },

        /**
         * initialize common ajax behavior
         */
        initAjax: function () {

            //TODO move this somewhere else (main controller?)

            var self = this,
                $body = $(document.body);

            //just before an ajax request
            $body.ajaxSend(function (event, request, settings) {
                helpers.loading();
            });

            //when an ajax request complete
            $body.ajaxComplete(function (event, request, settings) {
                helpers.loaded();

                if (settings.dataType === 'html') {
                    helpers._autoFx();
                }
            });

            //intercept errors
            $(document).ajaxError(function (event, request, settings, exception) {

                var errorMessage = __('Unknown Error');

                if (request.status === 404 && settings.type === 'HEAD') {

                    //consider it as a "test" to check if resource exists
                    return;

                }
                else if (request.status === 404 || request.status === 500) {

                    try {
                        // is it a common_AjaxResponse? Let's "duck type"
                        var ajaxResponse = $.parseJSON(request.responseText);
                        if (ajaxResponse !== null &&
                            typeof ajaxResponse['success'] !== 'undefined' &&
                            typeof ajaxResponse['type'] !== 'undefined' &&
                            typeof ajaxResponse['message'] !== 'undefined' &&
                            typeof ajaxResponse['data'] !== 'undefined') {

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
                else if (request.status === 403) {

                    window.location = context.root_url + 'tao/Main/logout';
                }

                feedback().error(errorMessage);
            });
        },

        /**
         * initialize common navigation
         */
        initNav: function () {

            //TODO move this somewhere else (layout/nav)

            //load the links target into the main container instead of loading a new page
            $(document).off('click', 'a.nav').on('click', 'a.nav', function () {
                try {
                    helpers._load(helpers.getMainContainerSelector(helpers.tabs), this.href);
                }
                catch (exp) {
                    return false;
                }
                return false;
            });
        }
    };

    return UiBootstrap;
});
