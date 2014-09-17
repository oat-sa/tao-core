<?php
use oat\tao\helpers\Template;

Template::inc('form_context.tpl', 'tao')
?>
<div class="main-container" data-tpl="tao/form_container.tpl">
    <h2><?=get_data('formTitle')?></h2>
    <div id="form-container" class="ui-widget-content ui-corner-bottom">
    
    	<?php if(has_data('errorMessage')):?>
    		<fieldset class='ui-state-error'>
    			<legend><strong><?=__('Error')?></strong></legend>
    			<?=get_data('errorMessage')?>
    		</fieldset>
    	<?php endif?>
    
    	<?=get_data('myForm')?>
    </div>
</div>