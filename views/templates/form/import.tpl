<?include(TAO_TPL_PATH .'header.tpl');?>
	
<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=get_data('formTitle')?>
</div>
<div id="form-container" class="ui-widget-content ui-corner-bottom">
	<?=get_data('myForm')?>
</div>

<script type="text/javascript">
$(document).ready(function(){
	$("#first_row_column_names_0").click(function(){
		$("#column_order").attr('disabled', this.checked);
	});
});
</script>

<?include(TAO_TPL_PATH .'footer.tpl');?>
