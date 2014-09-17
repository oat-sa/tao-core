define(['jquery', 'lodash', 'helpers', 'layout/tree', 'generis.actions', 'module'], 
function($, _, helpers, treeFactory, generisActions, module){
    return {
        start : function(){
            var sectionTreesData = module.config().sectionTreesData;

            if(sectionTreesData){
                for(var treeId in sectionTreesData){
                    var options = _.defaults(sectionTreesData[treeId],{
                        formContainer: helpers.getMainContainerSelector(),
                        actionId: treeId,
                        paginate: 30
                    });
                    var $treeElt = $('#tree-' + treeId);
                    if($treeElt.length){
    
                        treeFactory($treeElt, options.dataUrl, options);
                    }
                    //var tree = new GenerisTreeBrowserClass('#tree-' + treeId, options.dataUrl, options);
                    //generisActions.setMainTree(tree);
                }
            }
        }
    };
});


