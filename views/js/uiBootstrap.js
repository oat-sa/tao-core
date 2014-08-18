/**
 * UiBootstrap class enable you to run the navigation mode,
 * bind the events on the main components and initialize handlers
 *
 *
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require [helpers.js]
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @author Jehan Bihin (using class.js)
 */

define(['jquery', 'lodash', 'i18n', 'context', 'helpers', 'ui/feedback', 'layout/actions', 'jqueryui'], function($, _, __, context, helpers, feedback, actions) {
	
    var UiBootstrap = {
            init: function(options) {
                var self = this;
                var $sectionTrees = $('#section-trees');
                var $sectionActions = $('#section-actions');                

                var $tabs = $('#tabs');
                
                this.initAjax();
                this.initNav();
                
                //create tabs
                this.tabs = $tabs.tabs({
                        load: function(e, ui){
                            var $section = $(ui.tab);

                            context.section = $section.attr('id');

                            $sectionTrees.empty().hide();
                            $sectionActions.empty().hide();

                            if($section.data('trees')){
                                self.initTrees();
                            }

                            if($section.data('actions')){
                                self.initActions();
                            }
                        },
                        select: function(event, ui) {
                            $sectionTrees.empty().hide();
                            $sectionActions.empty().hide();

                            //empty other tabs
                            $tabs.children('ui-tabs-panel').each(function(){
                                if ($(this).attr('id') !== ui.panel.id) {
                                    $(this).empty();
                                }
                            });
                        }
                });

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

                    context.section = $section.attr('id');
 
                    $('.taotree', $section).each(function(){
                        var $treeElt = $(this);
                        
                        treeFactory($treeElt, $treeElt.data('url'), {
                            actions : {
                                'selectClass'    : $treeElt.data('action-selectclass'),
                                'selectInstance' : $treeElt.data('action-selectinstance'),
                                'moveInstance'   : $treeElt.data('action-moveinstance'),
                                'delete'         : $treeElt.data('action-delete')
                            }
                        });
                    });
                    actions.init();
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

                        feedback().error(errorMessage);
                    });
            },

            /**
             * initialize common naviguation
             */
            initNav: function(){
                    //load the links target into the main container instead of loading a new page
                    $(document).off('click', 'a.nav').on('click', 'a.nav', function() {
                            try{
                                    helpers._load(helpers.getMainContainerSelector(helpers.tabs), this.href);
                            }
                            catch(exp){return false;}
                            return false;
                    });
            },

            /**
             * initialize the tree component
             */
            initTrees: function(callback){

                    //left menu trees init by loading the tab content
                    if(this.tabs.length > 0){
                        var $sectionTrees = $('#section-trees');
                        
                        //get the link text of the selected tab
                        var section = $("li a[href=#" + $('.ui-tabs-panel')[this.tabs.tabs('option', 'selected')].id + "]:first").attr('id');
                        if (section !== undefined) {
                                $.ajax({
                                        url: context.root_url + 'tao/Main/getSectionTrees',
                                        type: "GET",
                                        data: {
                                                section: section,
                                                structure: context.shownStructure,
                                                ext: context.shownExtension
                                        },
                                        dataType: 'html',
                                        success: function(response){
                                                if(!response){
                                                        $sectionTrees.css({display: 'none'});
                                                } else if($sectionTrees.css('display') === 'none'){
                                                        $sectionTrees.css({display: 'block'});
                                                }
                                                $sectionTrees.html(response);
                                                if (callback !== undefined) {
                                                    callback();
                                                }
                                        }
                                });
                        }
                    }
            },

            /**
             * initialize the actions component
             */
            initActions: function(uri, classUri){
                return;
                var $sectionActions = $('#section-actions');
                //left menu actions init by loading the tab content
                if(this.tabs && this.tabs.length > 0){
                    if($('.actions-bar .action').length === 0){ 
                        //get the link text of the selected tab
                        var section = $("li a[href=#" + $('.ui-tabs-panel')[this.tabs.tabs('option', 'selected')].id + "]:first").attr('id');
                        $.ajax({
                            url: context.root_url + 'tao/Main/getSectionActions',
                            type: "GET",
                            data: {
                                    section: section,		
                                    structure: context.shownStructure,
                                    ext: context.shownExtension,
                                    uri: uri,
                                    classUri: classUri
                            },
                            dataType: 'html',
                            success: function(response){
                                    if(!response) {
                                        $sectionActions.css({display: 'none'});
                                    } else if($sectionActions.css('display') === 'none') {
                                        $sectionActions.css({display: 'block'});
                                    }
                                    $sectionActions.html(response);

                                    actions.init();
                                    //$(document).trigger('actionInitiated', [response]);
                            }
                        });
                    }
                }
            },

            /**
             * re-calculate the container size regarding the components content
             */
            initSize: function(){
                    //set up the container size
                    var $myPanel = $('.ui-tabs-panel')[this.tabs.tabs('option', 'selected')];
                    if($myPanel){
                        var uiTab = $myPanel.id;
                        var $tabContainer =  $("div#"+uiTab);
                        var $sectionActions = $('#section-actions');
                        var $sectionTrees = $('#section-trees');
                        if($sectionActions.html() == '' && $sectionTrees.html()  == '' && $tabContainer.css('width') === '79.5%' ){
                            $tabContainer.css({'width': '100%', 'left': 0});
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
