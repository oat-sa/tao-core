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
        'taoQtiDefaultRenderer' : '../../../taoQTI/views/js/qtiDefaultRenderer',
        'qtiClass' : ['../../../taoQTI/views/js/qtiItem/lib/class'],
        'raphael' : ['raphael.min'],
        'raphael-collision' : ['raphael/raphael-collision/raphael-collision'],
        'mediaElement' : ['../../../taoQTI/views/js/qtiDefaultRenderer/lib/mediaelement/mediaelement-and-player.min']
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
        'mediaElement' : {exports : 'MediaElementPlayer'}
    }
});