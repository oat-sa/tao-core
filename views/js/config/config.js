require.config({
    baseUrl : taobase_www + 'js',
    paths : {
        'jqueryUI' : ['jquery-ui-1.8.23.custom.min'],
        "text" : 'text/text',
        'lodash' : ['lodash.min'],
        'moment' : ['moment.min'],
        'taoQtiTest' : '../../../taoQtiTest/views/js',
        'cards' : '../../../taoQtiTest/views/js/cards',
        'ckeditor-jquery' : ['ckeditor/adapters/jquery'],
        'taoQtiItemCreator' : '../../../taoQTI/views/js/qtiCreator',
        'taoQtiItem' : '../../../taoQTI/views/js/qtiItem',
        'taoQtiRunner' : '../../../taoQTI/views/js/qtiRunner',
        'taoQtiItemRuntime' : '../../../taoQTI/views/js/runtime',
        'taoQtiDefaultRenderer' : '../../../taoQTI/views/js/qtiDefaultRenderer',
        'class' : ['class'],
        'raphael' : ['raphael.min'],
        'raphael-collision' : ['raphael/raphael-collision/raphael-collision'],
        'mediaElement' : ['../../../taoQTI/views/js/qtiDefaultRenderer/lib/mediaelement/mediaelement-and-player.min'],
        'mathJax' : ['../../../taoQTI/views/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML-full']
    },
    shim : {
        'jqueryUI' : ['jquery'],
        'jsTree/plugins/jquery.tree.contextmenu' : ['jsTree/jquery.tree'],
        'jsTree/plugins/jquery.tree.checkbox' : ['jsTree/jquery.tree'],
        'generis.tree.select' : ['generis.tree', 'jsTree/plugins/jquery.tree.checkbox'],
        'generis.tree.browser' : ['generis.tree', 'jsTree/plugins/jquery.tree.contextmenu'],
        'grid/tao.grid' : ['jquery.jqGrid-4.4.0/js/jquery.jqGrid.min', 'jquery.jqGrid-4.4.0/js/i18n/grid.locale-' + base_lang],
        'grid/tao.grid.downloadFileResource' : ['grid/tao.grid'],
        'grid/tao.grid.rowId' : ['grid/tao.grid'],
        'AsyncFileUpload' : ['jquery.uploadify/swfobject', 'jquery.uploadify/jquery.uploadify.v2.1.4.min'],
        'handlebars' : {exports : 'Handlebars'},
        'moment' : {exports : 'moment'},
        'ckeditor/ckeditor' : {exports : 'CKEDITOR'},
        'ckeditor-jquery' : ['ckeditor/ckeditor'],
        'json2' : {exports : 'JSON'},
        'class' : {exports : 'Class'},
        'mediaElement' : {
            exports : 'MediaElementPlayer',
            init : function(){
                MediaElementPlayer.pluginPath = '';//define the plugin swf path here
                return MediaElementPlayer;
            }
        },
        'mathJax' : {
            exports : "MathJax",
            init : function(){
                MathJax.Hub.Config({});//add mathJax config here
                MathJax.Hub.Startup.onload();
                return MathJax;
            }
        }
    }
});