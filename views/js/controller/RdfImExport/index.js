 require(['jquery', 'lodash', 'i18n', 'helpers', 'ui/feedback', 'uiForm'], function($, _, __, helpers, feedback, uiForm){

    var $form = $('#export'),
        $submitter = $form.find('.form-submitter'),
        $sent = $form.find(":input[name='" + $form.attr('name') + "_sent']");

    //overwrite the submit behaviour
    $submitter.off('click').on('click', function(e){

        e.preventDefault();

        if(!parseInt($sent.val())){
            return;
        }

        //prepare download params
        var $iframeContainer = $('#iframe-container'),
            params = {},
            instances = [];

        _.each($form.serializeArray(), function(param){
            if(param.name.indexOf('rdftpl_ns_') === 0){
                instances.push(param.name.substring(10));
            }else{
                params[param.name] = param.value;
            }
        });
        if (!instances.length) {
            feedback().error(__("Please select alteast one namespace"));
            return;
        }
        params.namespaces = instances;

        //build download url
        var url = helpers._url('export', 'RdfImExport', 'tao', params);

        //use the iframe to embed download in the page
        var $iframe = $('<iframe>', {src : url}).hide();
        $iframeContainer.empty().append($iframe);

    });

});