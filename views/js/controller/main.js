/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'i18n',
    'context',
    'helpers',
    'uiForm',
    'layout/section',
    'layout/actions',
    'layout/tree',
    'layout/version-warning',
    'layout/section-height',
    'layout/loading-bar',
    'layout/nav'
],
function ($, __, context, helpers, uiForm, section, actions, treeFactory, versionWarning, sectionHeight, loadingBar, nav) {
    'use strict';

    /**
     * This controller initialize all the layout components used by the backend : sections, actions, tree, loader, etc.
     * @exports tao/controller/main
     */
    return {
        start : function(){

            var $doc = $(document);

            versionWarning.init();

            //just before an ajax request
            $doc.ajaxSend(function () {
                loadingBar.start();
            });

            //when an ajax request complete
            $doc.ajaxComplete(function () {
                loadingBar.stop();
            });

            //navigation bindings
            nav.init();

            //initialize sections 
            section.on('activate', function(section){

                window.scrollTo(0,0);

                context.section = section.id;
               
                //initialize actions 
                actions.init(section.panel);

                switch(section.type){
                case 'tree':
                    section.panel.addClass('content-panel');
                    sectionHeight.init(section.panel);

                    //set up the tree
                    $('.taotree', section.panel).each(function(){
                        var $treeElt = $(this),
                            $actionBar = $('.tree-action-bar-box', section.panel);

                        var rootNode = $treeElt.data('rootnode');
                        var treeUrl = context.root_url;
                        if(/\/$/.test(treeUrl)){
                            treeUrl += $treeElt.data('url').replace(/^\//, '');
                        } else {
                            treeUrl += $treeElt.data('url');
                        }
                        treeFactory($treeElt, treeUrl, {
                            serverParameters : {
                                extension   : context.shownExtension,
                                perspective : context.shownStructure,
                                section     : context.section,
                                classUri	: rootNode ? rootNode : undefined
                            },
                            actions : {
                                'selectClass'    : $treeElt.data('action-selectclass'),
                                'selectInstance' : $treeElt.data('action-selectinstance'),
                                'moveInstance'   : $treeElt.data('action-moveinstance'),
                                'delete'         : $treeElt.data('action-delete')
                            }
                        });
                        $treeElt.on('ready.taotree', function() {
                            $actionBar.addClass('active');
                            sectionHeight.setHeights(section.panel);
                        });
                    });

                    $('.navi-container', section.panel).show();
                    break;
                case 'content' : 

                    //or load the content block
                    this.loadContentBlock();
                    
                    break;
                }
            })
            .init();


            //initialize legacy components
            helpers.init();
            uiForm.init();
        }
    };
});

