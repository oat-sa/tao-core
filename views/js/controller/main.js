define([
    'jquery',
    'context',
    'helpers',
    'uiForm',
    'layout/section',
    'layout/actions',
    'layout/tree',
    'controller/main/toolbar',
    'layout/version-warning',
    'layout/section-height'
],
function ($, context, helpers, uiForm, section, actions, treeFactory, toolbar, versionWarning, sectionHeight) {
    'use strict';

    return {
        start : function(){

            //initialize main components
            toolbar.setUp();

            versionWarning.init();
            sectionHeight.init();

            section
            .on('activate', function(section){
                window.scrollTo(0,0);

                context.section = section.id;
                
                actions.init(section.panel);

                switch(section.type){
                case 'tree':
                    section.panel.addClass('content-panel');
                    $('.taotree', section.panel).each(function(){
                        var $treeElt = $(this),
                            $actionBar = $('.tree-action-bar-box', section.panel);

                        var rootNode = $treeElt.data('rootnode');
                        treeFactory($treeElt, $treeElt.data('url'), {
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
                        });
                    });
                    break;
                case 'content' : 
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

