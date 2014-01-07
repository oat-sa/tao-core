require.config({

    baseUrl : '<?=TAOBASE_WWW?>js',
    catchError: true,
    
    config : {
        'context': {
            root_url        : '<?=ROOT_URL?>',
            base_url        : '<?=BASE_URL?>',
            taobase_www     : '<?=TAOBASE_WWW?>',
            base_www        : '<?=get_data('base_www')?>',
            base_lang       : '<?=get_data('lang')?>',
            locale          : '<?=get_data('locale')?>',
            extension       : '<?=get_data('extension')?>',
            module          : '<?=get_data('module')?>',
            action          : '<?=get_data('action')?>',
            shownExtension  : '<?=get_data('shownExtension')?>',
            shownStructure  : '<?=get_data('shownStructure')?>'
        }
    },
    
    paths : {
        'jquery'            : 'lib/jquery-1.8.0.min',
        'jqueryui'          : 'lib/jquery-ui-1.8.23.custom.min',
        'jquerytools'       : 'lib/jquery.tools.min',
        'text'              : 'lib/text/text',
        'json'              : 'lib/text/json',
        'store'             : 'lib/store/store.min',
        'select2'           : 'lib/select2/select2.min',
        'lodash'            : 'lib/lodash.min',
        'moment'            : 'lib/moment.min',
        'handlebars'        : 'lib/handlebars',
        'tpl'               : 'template',
        'ckeditor'          : 'lib/ckeditor/ckeditor',
        'ckeditor-jquery'   : 'lib/ckeditor/adapters/jquery',
        'json2'             : 'lib/json2',
        'class'             : 'lib/class',
        'jwysiwyg'          : 'lib/jwysiwyg/jquery.wysiwyg',
        'jsTree'            : 'lib/jsTree',
        'jqGrid'            : 'lib/jquery.jqGrid-4.4.0/js/jquery.jqGrid.min',
        'jquery.timePicker' : 'lib/jquery.timePicker',
        'jquery.cookie'     : 'lib/jquery.cookie',
        'attrchange'        : 'lib/attrchange',
        'raphael'           : 'lib/raphael.min',
        'spin'              : 'lib/spin.min',
        'raphael-collision' : 'lib/raphael/raphael-collision/raphael-collision',
        'mediaElement'      : '../../../taoQTI/views/js/qtiDefaultRenderer/lib/mediaelement/mediaelement-and-player.min',
        'mathJax'           : '../../../taoQTI/views/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML-full',
        'i18n_tr'           : '<?=BASE_URL?>locales/<?=get_data('locale')?>/messages_po',
    <?foreach (get_data('extensionsAliases') as $name => $path) :?>
        '<?=$name?>'        : '<?=$path?>',
    <?endforeach?>
        'taoQtiItemCreator' : '../../../taoQTI/views/js/qtiCreator',
        'taoQtiItem'        : '../../../taoQTI/views/js/qtiItem',
        'taoQtiRunner'      : '../../../taoQTI/views/js/qtiRunner',
        'taoQtiDefaultRenderer' : '../../../taoQTI/views/js/qtiDefaultRenderer',
        'taoQtiCommonRenderer' : '../../../taoQTI/views/js/qtiCommonRenderer',
        'qtiClass'          : '../../../taoQTI/views/js/qtiItem/lib/class',
        'jquery.fmRunner'   : '../../../filemanager/views/js/jquery.fmRunner'
   },
  
   shim : {
        'jqueryui'              : ['jquery'],
        'jquerytools'           : ['jquery'],
        'select2'               : ['jquery'],
        'jwysiwyg'              : ['jquery'],
        'jquery.cookie'         : ['jquery'],
        'jquery.timePicker'     : ['jquery'],
        'jsTree/plugins/jquery.tree.contextmenu' : ['lib/jsTree/jquery.tree'],
        'jsTree/plugins/jquery.tree.checkbox' : ['lib/jsTree/jquery.tree'],
        'generis.tree.select'   : ['generis.tree', 'lib/jsTree/plugins/jquery.tree.checkbox'],
        'generis.tree.browser'  : ['generis.tree', 'jsTree/plugins/jquery.tree.contextmenu'],
        'jqGrid'                : ['jquery', 'lib/jquery.jqGrid-4.4.0/js/i18n/grid.locale-<?=get_data('lang')?>'],
        'attrchange'            : ['jquery'],
        'grid/tao.grid'         : ['jqGrid'],
        'grid/tao.grid.downloadFileResource' : ['grid/tao.grid'],
        'grid/tao.grid.rowId'   : ['grid/tao.grid'],
        'AsyncFileUpload'       : ['lib/jquery.uploadify/swfobject', 'lib/jquery.uploadify/jquery.uploadify.v2.1.4.min'],
        'jquery.fmRunner'       : ['jquery', 'filemanager/fmRunner'],
        'filemanager/jqueryFileTree/jqueryFileTree' : ['jquery'],
        'wfEngine/wfApi/wfApi.min' : ['jquery'],
        'handlebars'            : { exports : 'Handlebars' },
        'moment'                : { exports : 'moment' },
        'ckeditor'              : { exports : 'CKEDITOR' },
        'ckeditor-jquery'       : ['ckeditor'],
        'json2'                 : { exports:'JSON'},
        'class'                 : { exports : 'Class'},
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