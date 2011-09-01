<?if(get_data('message')):?>
<div id="info-box" class="ui-corner-all auto-highlight auto-hide">
	<?=get_data('message')?>
</div>
<?endif?>

<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=__("My settings")?>
</div>
<div id="form-container" class="ui-widget-content ui-corner-bottom">
	<?=get_data('myForm')?>
</div>

<?if(get_data('optimizable')):?>
<?include('optimize.tpl');?>
<?endif;?> 

<? //include('update.tpl');?>  

<script type="text/javascript">
$(function(){
	$("#section-meta").empty();
	uiForm.initElements();
	
	<?if(get_data('reload')):?>
		window.location.reload();
	<?endif?>
});
</script>