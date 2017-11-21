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
    'taoTaskQueue/component/manager/manager',
    'taoTaskQueue/model/taskQueue'
],
function (module, $, __, context, helpers, uiForm, section, actions, treeFactory, versionWarning, sectionHeight, loadingBar, nav, search, taskQueueManagerFactory, taskQueue) {
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

            //console.log(JSON.stringify(_sampleLogCollection))

            var $plugin = $('<div class="plugin-box-element">').appendTo($('.plugin-box-menu'));
            var taskManager = taskQueueManagerFactory()
                .on('render', function(){
                    var self = this;
                })
                .on('remove', function(taskId){
                    taskQueue.archive(taskId);
                })
                .on('report', function(taskId){
                    taskQueue.get(taskId).then(function(task){
                        //show report in popup ???
                        console.log('show report', task);
                    });
                })
                .on('download', function(taskId){
                    taskQueue.download(taskId);
                })
                .render($plugin);


            taskQueue.on('pollAll', function(tasks){
                taskManager.loadData(tasks);
            });

            return;

            taskQueueInstance = taskQueueModel()
                .on('completed failed archived', function(){
                    //update the view manager
                    taskManager.update(this.getAllData());
                }).on('enqueued', function(taskData){
                    //update the view manager + animation
                    feedback('task created');
                    taskManager.animateInsertion(taskData);
                }).pollAll();//smart management of poll interval
        }
    };
});

