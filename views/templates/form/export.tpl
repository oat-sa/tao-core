
<div class="main-container flex-container-form-main" id="export-container">
    <h2><?=get_data('formTitle')?></h2>
    <?php if(has_data('myForm')):?>
    <div class="form-content">
        <?=get_data('myForm')?>
    </div>
    <?php endif;?>
</div>

<script>
    require([
                'jquery',
                'lodash',
                'i18n',
                'helpers',
                'uiForm',
                'ui/feedback',
                'jquery.fileDownload'
            ],
            function($, _, __, helpers, uiForm, feedback){

                var $form = $('#exportChooser'),
                        $submitter = $form.find('.form-submitter'),
                        $sent = $form.find(":input[name='" + $form.attr('name') + "_sent']");

                //by changing the format, the form is sent
                $form.on('change', ':radio[name=exportHandler]', function(){
                    $sent.val(0).remove();//ensure that the export is not triggered
                    uiForm.submitForm($form);
                });

                //overwrite the submit behaviour
                $submitter.off('click').on('click', function(e){
                    e.preventDefault();

                    if(parseInt($sent.val())){

                        //prepare download params
                        var $iframeContainer = $('#iframe-container'),
                                params = {},
                                instances = [];

                        _.each($form.serializeArray(), function(param){
                            if(param.name.indexOf('instances_') === 0){
                                instances.push(param.value);
                            }else{
                                params[param.name] = param.value;
                            }
                        });

                        params.instances = encodeURIComponent(JSON.stringify(instances));

                        $.ajax({
                            url : helpers._url("<?=get_data('export_action')?>", "<?=get_data('export_module')?>", "<?=get_data('export_extension')?>"),
                            data:  params,
                            type : 'POST',
                            dataType: "json"
                        }).done(function(response){
                            if(response.exported){
                                feedback().success(response.message);
                            } else {
                                feedback().error(response.message);
                            }
                        });
                    }

                });

            });
</script>