<div class="data-container-wrapper flex-container-full">
    <div class="grid-row">

        <div class="col-6 rdfImport">
            <h2>
                <?=__('Import')?>
            </h2>
            <div class="form-content ">
                <?=get_data('importForm')?>
            </div>
        </div>
        <div class="col-6 rdfExport">
            <h2>
                <?=__('Export ')?>
            </h2>
            <div class="form-content">
                <?=get_data('exportForm')?>
            </div>
        </div>
        <div class="ext-home-container">
        </div>
    </div>
</div>
<?php if(has_data('errorMessage')):?>
    <script>
        require(['ui/feedback'], function(feedback){
            feedback().error(<?=get_data('message')?>);
        });
    </script>
<?php endif ?>
<?php
if(get_data('importErrors')):?>

    <?php
    $msg = '<div>' . __('Error during file import') . '</div>'
         . '<ul>';
    foreach(get_data('importErrors') as $ierror) {
        $msg .= '<li>' . $ierror . '</li>';
    }
    $msg .= '</ul>';
    ?>

    <script>
        require(['ui/feedback'], function(feedback){
            feedback().error(<?=$msg?>);
        });
    </script>
<?php endif ?>
<?php if (has_data('download')):?>
	<iframe src="<?=get_data('download');?>" style="height: 0px;min-height: 0px"></iframe>
<?php endif;?>
