<?php 
use oat\tao\helpers\Template;
?>
<h2><?=get_data('formTitle')?></h2>
<div class="form-container" data-tpl="tao/import.tpl">

	<?if(get_data('importErrors')):?>
		<fieldset class='ui-state-error'>
			<legend><strong><?=(get_data('importErrorTitle'))?get_data('importErrorTitle'):__('Error during file import')?></strong></legend>
			<ul id='error-details'>
			<?foreach(get_data('importErrors') as $ierror):?>
				<li><?=$ierror->__toString()?></li>
			<?endforeach?>
			</ul>
		</fieldset>
	<?endif?>


	<?=get_data('myForm')?>
</div>

<script type="text/javascript">
require(['jquery'], function($) {
	
	//by changing the format, the form is sent
	$(":radio[name='importHandler']").change(function(){

		var form = $(this).parents('form');
		$(":input[name='"+form.attr('name')+"_sent']").remove();
		
		form.submit();
	});
	
	//for the csv import options
	$("#first_row_column_names_0").attr('checked', true);
	$("#first_row_column_names_0").click(function(){
            $("#column_order").attr('disabled', this.checked);
	});
});
</script>