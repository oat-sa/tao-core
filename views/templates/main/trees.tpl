<?if(get_data('trees')):?>
		
	<?foreach(get_data('trees') as $i => $tree):?>
		<div class="ui-widget-header ui-corner-top ui-state-default"><?=__((string)$tree['name'])?></div>
		<div id="tree-actions-<?=$i?>" class="tree-actions">
				<input type="text"   id="filter-content-<?=$i?>" value="*"  autocomplete='off'  size="10" title="<?=__('Use the * character to replace any string')?>" />
				<input type='button' id="filter-action-<?=$i?>"  value="<?=__("Filter")?>" 	  />
			</div>
		<div class="ui-widget ui-widget-content ui-corner-bottom">
			
			<div id="tree-<?=$i?>" ></div>
		</div>
	<?endforeach?>
	
<script type="text/javascript">
	
	$(function(){
		$(".ui-accordion").accordion({
			fillSpace: false,
			autoHeight: false,
			collapsible: true,
			active: 0,
			icons: { 'header': 'ui-icon-plus', 'headerSelected': 'ui-icon-minus' }
		});
		
		<?foreach(get_data('trees') as $i => $tree):?>
		new GenerisTreeClass('#tree-<?=$i?>', "<?=$tree['dataUrl']?>", {
			formContainer: 			getMainContainerSelector(UiBootstrap.tabs),
			actionId: 				"<?=$i?>",
			editClassAction: 		"<?=$tree['editClassUrl']?>",
			editInstanceAction: 	"<?=$tree['editInstanceUrl']?>",
			createInstanceAction: 	"<?=$tree['addInstanceUrl']?>",
			moveInstanceAction: 	"<?=$tree['moveInstanceUrl']?>",
			subClassAction: 		"<?=$tree['addSubClassUrl']?>",
			deleteAction: 			"<?=$tree['deleteUrl']?>",
			duplicateAction: 		"<?=$tree['duplicateUrl']?>",
			instanceClass:			"node-<?=get_data('instanceName')?>",
			instanceName:			"<?=get_data('instanceName')?>"
			<?if(get_data('openUri')):?>
			,selectNode:			"<?=get_data('openUri')?>"
			<?endif?>
		});
		<?endforeach?>
		
	});
	
</script>
<?endif?>