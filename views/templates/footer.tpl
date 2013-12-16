<script type="text/javascript">
<?if(has_data('reload')):?>

	$(function(){
		uiBootstrap.initTrees();
	});
<?endif?>

<?if(has_data('message')):?>
	$(function(){
		helpers.createMessage(<?=json_encode(get_data('message'))?>);
	});
<?endif?>
</script>