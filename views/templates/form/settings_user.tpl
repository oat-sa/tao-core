<?if(get_data('message')):?>
<div id="info-box" class="ui-corner-all auto-highlight auto-hide">
	<?=get_data('message')?>
</div>
<?endif?>

<div id="user-settings-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=get_data('formTitle')?>
</div>
<div id="user-settings-container" class="ui-widget-content ui-corner-bottom">
	<?=get_data('myForm')?>
</div>

<script type="text/javascript">
$(function(){
	$("#section-meta").empty();
	uiForm.initElements();

	<?if(get_data('reload')):?>
		window.location.reload();
	<?endif?>
});
</script>