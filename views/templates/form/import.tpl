<?php
use oat\tao\helpers\Template;
Template::inc('form.tpl', 'tao');
?>

<?php if(has_data('importErrorTitle')):?>
    <?php if(get_data('importErrors')):?>
        <?php
        $msg = '<div>' . get_data('importErrorTitle') ?get_data('importErrorTitle') :__('Error during file import') . '</div>';
        $msg .= '<ul>';
        foreach(get_data('importErrors') as $ierror) {
            $msg .= '<li><?=$ierror->__toString()?></li>';
        }
        $msg .= '</ul>';
        ?>
    <?php endif?>
    <script>
        require(['ui/feedback'], function(feedback){
            feedback().error(<?=$msg?>);
        });
    </script>
<?php endif ?>


<script type="text/javascript">
require([
    'jquery',
    'i18n',
    'util/url',
    'layout/actions',
    'test/core/taskQueue/stub/taskQueue',//TODO replace by 'core/taskQueue/taskQueue' when ready for backend integration
    'ui/taskQueueButton/standardButton'
], function($, __, urlHelper, actionManager, taskQueue, taskCreationButtonFactory) {

    var $container = $('.content-block'),
        $form = $('#import'),
        $oldSubmitter = $form.find('.form-submitter'),
        importUrl = urlHelper.route("<?=get_data('import_action')?>", "<?=get_data('import_module')?>", "<?=get_data('import_extension')?>");

    //TODO remove this when ready for backend integration
    var importUrl = '/tao/views/js/test/core/taskQueue/samples/newTaskCreationResult.json';
    taskQueue.setEndpoints({
        get: '/tao/views/js/test/core/taskQueue/samples/getSingle-completed-import.json'
    });

    /**
     * wrapped the old jstree API used to refresh the tree and optionally select a resource
     * @param {String} [uriResource] - the uri resource node to be selected
     */
    var refreshTree = function refreshTree(uriResource){
        actionManager.trigger('refresh', {
            uri : uriResource
        });
    };

    //find the old submitter and replace it with the new component
    var taskCreationButton = taskCreationButtonFactory({
        type : 'info',
        icon : 'import',
        title : __('Import'),
        label : __('Import'),
        taskQueue : taskQueue,
        taskCreationUrl : importUrl,
        taskCreationData : function getTaskCreationData(){
            return $form.serializeArray();
        },
        taskReportContainer : $container
    }).on('finished', function(result){
        console.log(result);
        if (result.task
                && result.task.report
                && _.isArray(result.task.report.children)
                && result.task.report.children.length
                && result.task.report.children[0]) {
            if(result.task.report.children[0].data
                    && result.task.report.children[0].data.uriResource){
                this.selectedNode = result.task.report.children[0].data.uriResource;
                this.displayReport(result.task.report, __('Import Completed'));
            }else{
                this.displayReport(result.task.report, __('Error'));
            }
        }
    }).on('continue', function(){
        refreshTree(this.selectedNode);
    }).on('error', function(err){
        //format and display error message to user
        feedback().error(err);
    }).render($oldSubmitter.closest('.form-toolbar'));

    //replace the old submitter with the new one and apply its style
    $oldSubmitter.replaceWith(taskCreationButton.getElement().css({float: 'right'}));

	//by changing the format, the form is sent
	$(":radio[name='importHandler']").change(function(){

		var form = $(this).parents('form');
		$(":input[name='"+form.attr('name')+"_sent']").remove();

		form.submit();
	});

	//for the csv import options
	$("#first_row_column_names_0").attr('checked', true).click(function(){
        if ( this.checked ){
            $("#column_order").attr('disabled','disabled');
        }else{
            $("#column_order").removeAttr('disabled');
        }
	});

        //show the csv fields mapping combos
    var $mapper =  $("#property_mapping > .property-edit-container");
    $mapper.show();

    if ($mapper.length) {
        $('#formats').hide();
    }
});
</script>
