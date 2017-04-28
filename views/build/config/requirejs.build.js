require.config({

    baseUrl : '../js',
    paths : {

//require-js plugins
        'text'              : 'lib/text/text',
        'json'              : 'lib/text/json',
        'css'               : 'lib/require-css/css',
        'tpl'               : 'tpl',

//jquery and plugins
        'jquery'            : 'lib/jquery-1.8.0.min',
        'jqueryui'          : 'lib/jquery-ui-1.8.23.custom.min',
        'select2'           : 'lib/select2/select2.min',
        'jquery.autocomplete'  : 'lib/jquery.autocomplete/jquery.autocomplete',
        'jwysiwyg'          : 'lib/jwysiwyg/jquery.wysiwyg',
        'jquery.tree'       : 'lib/jsTree/jquery.tree',
        'jquery.timePicker' : 'lib/jquery.timePicker',
        'jquery.cookie'     : 'lib/jquery.cookie',
        'nouislider'        : 'lib/sliders/jquery.nouislider',
        'jquery.trunc'		: 'lib/jquery.badonkatrunc',
        'jquery.fileDownload'  : 'lib/jquery.fileDownload',
        'qtip'              : 'lib/jquery.qtip/jquery.qtip',

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
        'html5-history-api' : 'lib/history/history',

        'pdfjs-dist/build/pdf'        : 'lib/pdfjs/build/pdf',
        'pdfjs-dist/build/pdf.worker' : 'lib/pdfjs/build/pdf.worker',
        'mathJax'           : '../../../taoQtiItem/views/js/mathjax/MathJax',
        'ckeditor'          : 'lib/ckeditor/ckeditor',
        'interact'          : 'lib/interact',
        'd3'                : 'lib/d3js/d3.min',
        'c3'                : 'lib/c3js/c3.min',

//optimizer needed
        'css-builder'       : 'lib/require-css/css-builder',
        'normalize'         : 'lib/require-css/normalize',

//stub
        'i18ntr'            : '../locales/en-US'
   },

   shim : {
        'moment'                : { exports : 'moment' },
        'ckeditor'              : { exports : 'CKEDITOR' },
        'ckeditor-jquery'       : ['ckeditor'],
        'class'                 : { exports : 'Class'},
        'c3'                    : { deps : ['css!lib/c3js/c3.css']}
    }
});
