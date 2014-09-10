<?php
use oat\tao\helpers\Template;
?>
<div class="data-container-wrapper grid-row">

    <div class="col-6 rdfImport">
        <h2>
    		<?=__('Import')?>
    	</h2>
    	<div class="form-content ">
    	<?if(get_data('importErrors')):?>
    		<fieldset class='ui-state-error'>
    			<legend><strong><?=__('Error during file import')?></strong></legend>
    			<ul id='error-details'>
    			<?php foreach(get_data('importErrors') as $ierror):?>
    				<li><?=$ierror?></li>
    			<?php endforeach; ?>
    			</ul>
    		</fieldset>
    	<?endif?>
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
<?if (has_data('download')):?>
	<iframe src="<?=get_data('download');?>" style="height: 0px;min-height: 0px"></iframe>
<?php endif;?>
