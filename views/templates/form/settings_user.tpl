<?if(get_data('message')):?>
<div id="info-box" class="ui-corner-all auto-highlight auto-hide">
	<?=get_data('message')?>
</div>
<?endif?>

<div class="containerDisplay main-container" id="settingsUserProperties">
	<span class="title"><?=__("My settings")?></span>
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
