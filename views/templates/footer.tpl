<script type="text/javascript">
	var ctx_extension 	= "<?=get_data('extension')?>";
	var ctx_module 		= "<?=get_data('module')?>";
	var ctx_action 		= "<?=get_data('action')?>";
<?if(get_data('reload')):?>

	$(function(){
		uiBootstrap.initTrees();
	});
<?endif?>
</script>