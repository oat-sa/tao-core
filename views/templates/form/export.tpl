<div class="main-container flex-container-form-main" id="export-container">
    <h2><?=get_data('formTitle')?></h2>
    <?php if(has_data('myForm')):?>
    <div class="form-content">
        <?=get_data('myForm')?>
    </div>
    <?php endif;?>
</div>

<div class="data-container-wrapper col-6">
    <div id="task-list"></div>
</div>




<script>
    require([
                'jquery',
                'lodash',
                'i18n',
                'helpers',
                'uiForm',
                'ui/tooltip',
                'ui/feedback',
                'ui/taskQueue/table',
                'jquery.fileDownload'
        ],
            function($, _, __, helpers, uiForm, tooltip, feedback, taskQueueTableFactory){
                'use strict';

                var $queueArea = $('#task-list');

                if("<?= get_data('asynchronous')?>"){
                    var taskQueueTable = taskQueueTableFactory({
                        rows : 10,
                        context : "<?=get_data('context')?>",
                        dataUrl : helpers._url('getTasks', 'TaskQueueData', 'tao'),
                        statusUrl : helpers._url('getStatus', 'TaskQueueData', 'tao'),
                        removeUrl : helpers._url('archiveTask', 'TaskQueueData', 'tao'),
                        downloadUrl : helpers._url('download', 'TaskQueueData', 'tao')
                    })
                        .init()
                        .render($queueArea);

                }

                var $form = $('#exportChooser'),
                    updateSubmitter,
                    $submitter = $form.find('.form-submitter'),
                    $sent = $form.find(":input[name='" + $form.attr('name') + "_sent']");

                //by changing the format, the form is sent
                $form.on('change', ':radio[name=exportHandler]', function(){
                    $sent.val(0).remove();//ensure that the export is not triggered
                    uiForm.submitForm($form);
                });

                /**
                 * toggle the state of the submitter (active/disable) according to number of checked elements
                 * @param $container
                 */
                updateSubmitter = function updateSubmitter(){
                    if($form.find('.form-group :checkbox:checked').length > 0){
                        $submitter.removeClass('disabled');
                    }else{
                        $submitter.addClass('disabled');
                    }
                };

                //if the export form has some elements to select, activate the submitter toggler
                if($form.find('.form-group :checkbox').length){
                    updateSubmitter();
                    $form.on('change', ':checkbox', updateSubmitter);
                }

                //manually init the tooltip
                tooltip($form);

                //overwrite the submit behaviour
                $submitter.off('click').on('click', function(e){
                    //prepare download params
                    var params = {},
                        instances = [];

                    e.preventDefault();

                    if(!$submitter.hasClass('disabled') && parseInt($sent.val())){

                        _.each($form.serializeArray(), function(param){
                            if(param.name.indexOf('instances_') === 0){
                                instances.push(param.value);
                            }else if(param.name.indexOf('classes_') === 0){
                                classes.push(param.value);
                            } else {
                                params[param.name] = param.value;
                            }
                        });

                        params.instances = encodeURIComponent(JSON.stringify(instances));
                        params.classes = encodeURIComponent(JSON.stringify(classes));


                        if("<?= get_data('asynchronous')?>"){
                            $.ajax({
                                url : helpers._url("<?=get_data('export_action')?>", "<?=get_data('export_module')?>", "<?=get_data('export_extension')?>"),
                                data:  params,
                                type : 'POST',
                                dataType: "json"
                            }).done(function(response){
                                if(response.exported){
                                    feedback().success(response.message);
                                    taskQueueTable.trigger('reload')
                                } else {
                                    feedback().error(response.message);
                                }
                            });

                        } else {
                            $.fileDownload(helpers._url("<?=get_data('export_action')?>", "<?=get_data('export_module')?>", "<?=get_data('export_extension')?>"), {
                                httpMethod: 'POST',
                                data: params,
                                failCallback: function (html) {
                                    $('#export-container').html(html);
                                    $('#import-continue').remove();
                                }
                            });
                        }


                    }

                });

            });
</script>