<?if(get_data('trees')):?>
	
<div id="tree-accordion" class="ui-accordion ui-widget ui-helper-reset">
		
	<?foreach(get_data('trees') as $i => $tree):?>
	
	<h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all">
	    <span class="ui-icon"></span>
	     <a href="#"><?=__((string)$tree['name'])?></a>
	  </h3>
	<div class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" style="padding:0em 0em 1em 1em;">
		<div id="tree-<?=$i?>" ></div>
		<div id="tree-actions-<?=$i?>" class="tree-actions">
			<input type="text"   id="filter-content-<?=$i?>" value="*"  autocomplete='off'  size="10" title="<?=__('Use the * character to replace any string')?>" />
			<input type='button' id="filter-action-<?=$i?>"  value="<?=__("Filter")?>" 	  /><br />
			<input type='button' id="open-action-<?=$i?>"    value='<?=__("Open all")?>'  />
			<input type='button' id="close-action-<?=$i?>"   value='<?=__("Close all")?>' />
		</div>
	</div>
		
	<?endforeach?>
	
</div>
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