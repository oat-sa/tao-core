define(function(){
    return {
        'Main': {
            'actions' : {
                'getSectionActions' : 'controller/main/actions',
                'getSectionTrees' : 'controller/main/trees'
            }
        },
        'ExtensionsManager' : {
            'actions' : {
                'index' : 'controller/settings/extensionManager'
            }
        },
        'Optimize' : {
            'actions' : {
                'index' : 'controller/settings/optimizer'
            }
        },
        'Lists' : {
            'actions' : {
                'index' : 'controller/list/index'
            }
        }
    };
});
