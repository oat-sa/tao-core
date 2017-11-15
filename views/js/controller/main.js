/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
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
    'layout/nav',
    'layout/search',
    'taoTaskQueue/component/manager/manager'
],
function (module, $, __, context, helpers, uiForm, section, actions, treeFactory, versionWarning, sectionHeight, loadingBar, nav, search, taskQueueManagerFactory) {
    'use strict';

    var _sampleLogCollection = [
        {
            id: 'rdf#i1508337970199318643',
            task_name: 'Task Name',
            label: 'Task label',
            status: 'completed',
            owner: 'userId',
            created_at: '1510149684',//timezone ?
            updated_at: '1510149694',
            file: false,//suppose
            category: 'import',
            report : {
                type : 'success',
                message : 'completed task rdf#i1508337970199318643',
                data : null,
                children: []
            }
        },
        {
            id: 'rdf#i15083379701993186432222',
            task_name: 'Task Name 2',
            label: 'Task label 2',
            status: 'in_progress',
            owner: 'userId',
            created_at: '1510149584',//timezone ?
            updated_at: '1510149574',
            file: false,
            category: 'publish',//d
            report : {
                type : 'info',
                message : 'running task rdf#i15083379701993186432222',
                data : null,//download url ? task context ?
                children: []
            }
        },
        {
            id: 'rdf#i1508337970190342',
            task_name: 'Task Name 2',
            label: 'Task label 2',
            status: 'failed',
            owner: 'userId',
            created_at: '1510149584',//timezone ?
            updated_at: '1510049574',
            file: true,//suppose
            category: 'export',//d
            report : {
                type : 'error',
                message : 'running task rdf#i1508337970190342',
                data : null,//download url ? task context ?
                children: []
            }
        }
    ];

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

            //search component
            search.init();

            //initialize sections 
            section.on('activate', function(section){

                window.scrollTo(0,0);

                // quick work around issue in IE11
                // IE randomly thinks there is no id and throws an error
                // I know it's not logical but with this 'fix' everything works fine
                if(!section || !section.id) {
                    return;
                }

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
                        var treeActions = {};
                        $.each($treeElt.data('actions'), function (key, val) {
                            if (actions.getBy(val)) {
                                treeActions[key] = val;
                            }
                        });
                        
                        if(/\/$/.test(treeUrl)){
                            treeUrl += $treeElt.data('url').replace(/^\//, '');
                        } else {
                            treeUrl += $treeElt.data('url');
                        }
                        treeFactory($treeElt, treeUrl, {
                            serverParameters : {
                                extension    : context.shownExtension,
                                perspective  : context.shownStructure,
                                section      : context.section,
                                classUri     : rootNode ? rootNode : undefined
                            },
                            actions : treeActions
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

            var $plugin = $('<div class="plugin-box-element">').appendTo($('.plugin-box-menu'));
            var taskManager = taskQueueManagerFactory({}, _sampleLogCollection)
                .on('render', function(){
                    var self = this;

                })
                .render($plugin);
        }
    };
});

