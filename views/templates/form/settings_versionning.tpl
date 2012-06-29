<?if(get_data('message')):?>
	<div id="info-box" class="ui-corner-all auto-highlight auto-hide">
		<?=get_data('message')?>
	</div>
<?endif?>

<div class="containerDisplay" id="settingsVersionning">
	<span class="title"><?= __('Versionning') ?></span>
<?=get_data('myForm')?>
</div>

<?include(TAO_TPL_PATH.'footer.tpl')?>

<script type="text/javascript">
$(function(){
	uiForm.initElements();

	<?if(get_data('reload')):?>
		window.location.reload();
	<?endif?>
});
</script>