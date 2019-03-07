<div class="main-container flex-container-form-main" id="export-container">
    <h2><?=get_data('formTitle')?></h2>
    <?php if(has_data('exportForm')):?>
    <div class="form-content">
        <?=get_data('exportForm')?>
    </div>
    <?php endif;?>
</div>

<script>
    require([
            'jquery',
            'lodash',
            'i18n',
            'util/url',
            'uiForm',
            'ui/tooltip',
            'ui/feedback',
            'core/taskQueue/taskQueue',
            'ui/taskQueueButton/standardButton',
            'jquery.fileDownload'
        ],
        function($, _, __, urlHelper, uiForm, tooltip, feedback, taskQueue, taskCreationButtonFactory){
            'use strict';

            var $container = $('#export-container'),
                $form = $('#exportChooser'),
                updateSubmitter,
                exportUrl = urlHelper.route("<?=get_data('export_action')?>", "<?=get_data('export_module')?>", "<?=get_data('export_extension')?>"),
                $oldSubmitter = $form.find('.form-submitter'),
                $sent = $form.find(":input[name='" + $form.attr('name') + "_sent']");

            //find the old submitter and replace it with the new component
            var taskCreationButton = taskCreationButtonFactory({
                type : 'info',
                icon : 'export',
                title : __('Export'),
                label : __('Export'),
                taskQueue : taskQueue,
                taskCreationUrl : exportUrl,
                taskCreationData : function getTaskCreationData(){
                    var params = {};
                    var instances = [];

                    _.forEach($form.serializeArray(), function(param){
                        if(param.name.indexOf('instances_') === 0){
                            instances.push(param.value);
                        }else{
                            params[param.name] = param.value;
                        }
                    });

                    params.instances = encodeURIComponent(JSON.stringify(instances));
                    return params;
                },
                taskReportContainer : $container
            }).on('error', function(err){
                //format and display error message to user
                feedback().error(err);
            }).render($oldSubmitter.closest('.form-toolbar'));

            //replace the old submitter with the new one and apply its style
            $oldSubmitter.replaceWith(taskCreationButton.getElement().css({float: 'right'}));

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
                    taskCreationButton.enable();
                }else{
                    taskCreationButton.disable();
                }
            };

            //if the export form has some elements to select, activate the submitter toggler
            if($form.find('.form-group :checkbox').length){
                updateSubmitter();
                $form.on('change', ':checkbox', updateSubmitter);
            }

            //manually init the tooltip
            tooltip.lookup($form);
        });
</script>