<?if(get_data('trees')):?>

	<?foreach(get_data('trees') as $i => $tree):?>
		<div class="tree-block">
			<div class="ui-widget-header ui-corner-top ui-state-default"><?=__((string)$tree['name'])?></div>
			<div id="tree-actions-<?=$i?>" class="tree-actions">
				<input type="text"   id="filter-content-<?=$i?>" value="*"  autocomplete='off'  size="10" title="<?=__('Use the * character to replace any string')?>" />
				<input type='button' id="filter-action-<?=$i?>"  value="<?=__("Filter")?>" />
				<input type='button' id="filter-cancel-<?=$i?>"  value="<?=__("Finish")?>" class="ui-helper-hidden ui-state-error"/>
			</div>
			<div class="ui-widget ui-widget-content ui-corner-bottom">
				<div id="tree-<?=$i?>"></div>
			</div>
		</div>
	<?endforeach?>

<script type="text/javascript">

	$(function(){
		<?foreach(get_data('trees') as $i => $tree):?>
		new GenerisTreeClass('#tree-<?=$i?>', "<?=$tree['dataUrl']?>", {
			formContainer: 			getMainContainerSelector(UiBootstrap.tabs),
			actionId: 				"<?=$i?>",
			editClassAction: 		"<?=$tree['editClassUrl']?>",
			editInstanceAction: 	"<?=$tree['editInstanceUrl']?>",
			<?if (isset($tree['addInstanceUrl'])):?>createInstanceAction: 	"<?=$tree['addInstanceUrl']?>",<?endif;?>
			<?if (isset($tree['moveInstanceUrl'])):?>moveInstanceAction: 	"<?=$tree['moveInstanceUrl']?>",<?endif;?>
			<?if (isset($tree['addSubClassUrl'])):?>subClassAction: 		"<?=$tree['addSubClassUrl']?>",<?endif;?>
			deleteAction: 			"<?=$tree['deleteUrl']?>",
			<?if (isset($tree['duplicateUrl'])):?>duplicateAction: 		"<?=$tree['duplicateUrl']?>",<?endif;?>
			instanceClass:			"node-<?=get_data('instanceName')?>",
			instanceName:			"<?=(isset($tree['className'])) ? mb_strtolower(__($tree['className']), TAO_DEFAULT_ENCODING) : get_data('instanceName') ?>",
			paginate:				10
			<?if(get_data('openUri')):?>
			,selectNode:			"<?=get_data('openUri')?>"
			<?endif?>
		});
		<?endforeach?>

	});

</script>
<?endif?>