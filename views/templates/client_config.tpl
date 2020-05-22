<?php
use oat\tao\model\security\xsrf\TokenService;
?>
require.config({

    baseUrl : '<?=get_data('tao_base_www')?>js',
    catchError: true,
    waitSeconds: <?=get_data('client_timeout')?>,
<?php if(get_data('buster')):?>
    urlArgs : "buster=<?=get_data('buster')?>",
<?php endif; ?>

    config : {
        context : <?=get_data('context')?>,
        text: {
            useXhr: function(){ return true; },
        },
        'ui/themes' : <?= get_data('themesAvailable') ?>,
        'core/tokenHandler' : <?=get_data(TokenService::JS_DATA_KEY)?>,

//dynamic lib config
    <?php foreach (get_data('libConfigs') as $name => $config) :?>
        '<?=$name?>'        : <?=json_encode($config)?>,
    <?php endforeach?>
    },
    onNodeCreated: function(node, config, name, url){
<?php if(get_data('crossorigin')):?>
        node.setAttribute('crossorigin', 'anonymous');
<?php endif; ?>
    },
    paths : {
//require-js plugins
        'text'              : 'lib/text/text',
        'json'              : 'lib/text/json',
        'css'               : 'lib/require-css/css',
        'tpl'               : 'tpl',
//jquery and plugins
        'jquery'            : '../node_modules/jquery/jquery',
        'jquery.autocomplete'  : 'lib/jquery.autocomplete/jquery.autocomplete',
        'jquery.tree'       : 'lib/jsTree/jquery.tree',
        'jquery.timePicker' : 'lib/jquery.timePicker',
        'jquery.cookie'     : 'lib/jquery.cookie',
        'nouislider'        : 'lib/sliders/jquery.nouislider',
        'jquery.fileDownload'  : 'lib/jquery.fileDownload',
// select2
        'select2'           : '../node_modules/@oat-sa/tao-core-libs/dist/select2',
        'select2-origin'    : '../node_modules/select2',
//polyfills
        'polyfill'          : 'lib/polyfill',
        'url-polyfill'      : '../node_modules/url-polyfill/url-polyfill',
//libs
        'lodash'            : '../node_modules/lodash/lodash',
        'async'             : 'lib/async',
        'moment'            : '../node_modules/moment/min/moment-with-locales',
        'handlebars'        : '../node_modules/handlebars/dist/handlebars',
        'class'             : 'lib/class',
        'raphael'           : 'lib/raphael/raphael',
        'scale.raphael'     : 'lib/raphael/scale.raphael',
        'spin'              : 'lib/spin.min',
        'pdfjs-dist/build/pdf'        : 'lib/pdfjs/build/pdf',
        'pdfjs-dist/build/pdf.worker' : 'lib/pdfjs/build/pdf.worker',
        'mathJax'           : [
            '../../../taoQtiItem/views/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML-full',
            '../../../taoQtiItem/views/js/MathJaxFallback'
        ],
        'ckeditor'          : '../node_modules/@oat-sa/tao-core-shared-libs/lib/ckeditor/ckeditor',
        'interact'          : '../node_modules/interactjs/dist/interact',
        'd3'                : 'lib/d3js/d3',
        'c3'                : 'lib/c3js/c3',
//locale loader
        'i18ntr'            : '../locales/<?=get_data('locale')?>',
//backward compat aliases
        'router'            : 'core/router',
//extension aliases, and controller loading in prod mode
    <?php foreach (get_data('extensionsAliases') as $name => $path) :?>
        '<?=$name?>'        : '<?=$path?>',
    <?php endforeach?>
        'lib/uuid'          : '../node_modules/@oat-sa/tao-core-libs/dist/uuid',
        'core'              : '../node_modules/@oat-sa/tao-core-sdk/dist/core',
        'util'              : '../node_modules/@oat-sa/tao-core-sdk/dist/util',
        'ui'                : '../node_modules/@oat-sa/tao-core-ui/dist'
    },
   shim : {
        'jqueryui'              : { deps : ['jquery'] },
        'moment'                : { exports : 'moment' },
        'handlebars'            : { exports : 'Handlebars' },
        'ckeditor'              : { exports : 'CKEDITOR' },
        'ckeditor-jquery'       : ['ckeditor'],
        'class'                 : { exports : 'Class'},
        'c3'                    : { deps : ['d3', 'css!lib/c3js/c3.css']},
	'lib/flatpickr/l10n/index' : { deps: ['lib/flatpickr/flatpickr'] },
        'mathJax' : {
            exports : "MathJax",
            init : function(){
                if(window.MathJax){
                    MathJax.Hub.Config({showMathMenu:false, showMathMenuMSIE:false,
                        menuSettings: { inTabOrder: false } });
                    MathJax.Hub.Startup.onload();
                    return MathJax;
                }
            }
        }
    }
});
