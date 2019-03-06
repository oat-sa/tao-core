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
        'jquery'            : 'lib/jquery-1.9.1',
        'jqueryui'          : 'lib/jquery-ui-1.9.2.custom.min',
        'select2'           : 'lib/select2/select2.min',
        'jquery.autocomplete'  : 'lib/jquery.autocomplete/jquery.autocomplete',
        'jquery.tree'       : 'lib/jsTree/jquery.tree',
        'jquery.timePicker' : 'lib/jquery.timePicker',
        'jquery.cookie'     : 'lib/jquery.cookie',
        'nouislider'        : 'lib/sliders/jquery.nouislider',
        'jquery.fileDownload'  : 'lib/jquery.fileDownload',
//polyfills
        'polyfill'          : 'lib/polyfill',
//libs
        'lodash'            : 'lib/lodash.min',
        'async'             : 'lib/async',
        'moment'            : 'lib/moment-with-locales.min',
        'handlebars'        : 'lib/handlebars',
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
        'ckeditor'          : 'lib/ckeditor/ckeditor',
        'interact'          : 'lib/interact',
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
   },
   shim : {
        'jqueryui'              : { deps : ['jquery'] },
        'moment'                : { exports : 'moment' },
        'ckeditor'              : { exports : 'CKEDITOR' },
        'ckeditor-jquery'       : ['ckeditor'],
        'class'                 : { exports : 'Class'},
        'c3'                    : { deps : ['d3', 'css!lib/c3js/c3.css']},
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
