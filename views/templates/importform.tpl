<?include('header.tpl');?>
	
<script type="text/javascript">
$(function(){
	$("#first_row_column_names_0").click(function(){
		$("#column_order").attr('disabled', this.checked);
	});
});
</script>

<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=get_data('formTitle')?>
</div>
<div id="form-container" class="ui-widget-content ui-corner-bottom">
	<?=get_data('myForm')?>
</div>

<?include('footer.tpl');?>
