require.config({
   paths : {
        'jquery' : 'lib/jquery-1.8.0.min',
        'jqueryui' : 'lib/jquery-ui-1.8.23.custom.min',
        'jquerytools' : 'lib/jquery.tools.min',
        'text' : 'lib/text/text',
        'lodash' : 'lib/lodash.min',
        'handlebars' : 'lib/handlebars',
        'class' : 'lib/class',
        'raphael' : 'lib/raphael.min',
        'raphael-collision' : 'lib/raphael/raphael-collision/raphael-collision',
        'mediaElement' : '../../../taoQTI/views/js/qtiDefaultRenderer/lib/mediaelement/mediaelement-and-player.min',
        'mathJax' : '../../../taoQTI/views/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML-full',
        'i18n_tr' : '../../locales/en-US/messages_po',
        'tao' : '../../../tao/views/js',
        'taoQTI' : '../../../taoQTI/views/js',
        'taoQtiItem' : '../../../taoQTI/views/js/qtiItem',
        'taoQtiRunner' : '../../../taoQTI/views/js/qtiRunner',
        'taoQtiDefaultRenderer' : '../../../taoQTI/views/js/qtiDefaultRenderer'
    },
    shim : {
        'jqueryui' : ['jquery'],
        'jquerytools' : ['jquery'],
        'AsyncFileUpload' : ['lib/jquery.uploadify/swfobject', 'lib/jquery.uploadify/jquery.uploadify.v2.1.4.min'],
        'handlebars' : {exports : 'Handlebars'},
        'json2' : {exports : 'JSON'},
        'class' : {exports : 'Class'},
        'mediaElement' : {
            exports : 'MediaElementPlayer',
            init : function(){
                MediaElementPlayer.pluginPath = '';//define the plugin swf path here
                return MediaElementPlayer;
            }
        }
    }
});