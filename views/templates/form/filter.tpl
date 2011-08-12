
<?include(dirname(__FILE__).'/../portail/header.tpl');?>

<div id="delivery-left-container">
	
	<?foreach($properties as $property):?>
	<div id="group-container" class="data-container">
		<div class="ui-widget ui-state-default ui-widget-header ui-corner-top container-title" >
			<?=__('Add to group')?>
		</div>
		<div class="ui-widget ui-widget-content container-content">
			<div id="tree-<?= md5($property->uriResource) ?>"></div>
		</div>
		<div class="ui-widget ui-widget-content ui-state-default ui-corner-bottom" style="text-align:center; padding:4px;">
			<input id="filter-action-group" type="button" value="<?=__('Filter')?>" />
		</div>
	</div>
	<?endforeach?>
	
	<div class="breaker"></div>
	<?if(!get_data('myForm')):?>
		<input type='hidden' name='uri' value="<?=get_data('uri')?>" />
		<input type='hidden' name='classUri' value="<?=get_data('classUri')?>" />
	<?endif?>
	
	<script type="text/javascript">
	$(document).ready(function(){
		var filterTrees = new Array ();
		var url = '';
		if(ctx_extension){
			url = root_url + '/' + ctx_extension + '/' + ctx_module + '/';
		}
		var getUrl = root_url + 'taoItems/items/getFilteredInstancesPropertiesValues';
		<?foreach($properties as $property):?>
		filterTrees['<?= tao_helpers_Uri::encode($property->uriResource) ?>'] = new GenerisTreeFormClass('#tree-<?= md5($property->uriResource) ?>', getUrl, {
			'actionId': 'filter',
			'serverParameters' : {
				'propertyUri' : '<?= $property->uriResource ?>',
				'classUri' : '<?= $clazz->uriResource ?>'
			}
		});
		<?endforeach?>

		$('#filter-action-group').click (function () {
			var filter = new Array ();
			//Get the checked nodes
			for (var treeId in filterTrees){
				var checked = filterTrees[treeId].getChecked();
				for (var i in checked){
					filter.push ({
						property_uri : treeId,
						value : checked[i]
					});
				}
			}
			//Refresh all trees with the new filter
			for (var treeId in filterTrees){
				// Set the server parameter
				// By setting the server parameter the tree will reload itself
				filterTrees[treeId].setServerParameter('filter', filter, true);
			}
			//Refresh the result brol
			$.getJSON (root_url+'taoItems/items/searchInstances'
				,{
					'classUri' : '<?= $clazz->uriResource ?>'
					, 'filter' : filter
				}
				, function (DATA) {

					// empty the grid
					var myGrid = $("#result-list"); // the variable you probably have already somewhere
					var gridBody = myGrid.children("tbody");
					var firstRow = gridBody.children("tr.jqgfirstrow");
					gridBody.empty().append(firstRow);
					
					for(var i in DATA) {
						var row = {'id':i};
						for (var j in DATA[i].properties) {
							if (DATA[i].properties[j] == null){
								row['property_'+j] = '';
							} else {
								row['property_'+j] = DATA[i].properties[j];
							}
						}
						jQuery("#result-list").jqGrid('addRowData', i+1, row);
					}
				}
			);
		});
	});
	</script>
		
</div>

<!-- TABLE of restults -->
<table id="result-list"></table>
<div id="result-list-pager"></div>
<div class="ui-state-error" style="display:none"><?=__('No result found')?></div>
<br />

<?if(get_data('found')):?>
<script type="text/javascript">
$(document).ready(function(){
	var properties = ['id',
	<?foreach(get_data('properties') as $uri => $property):?>
		 '<?=$property->getLabel()?>',
	<?endforeach?>
		__('Actions')
	];
	
	var model = [
		{name:'id',index:'id', width: 25, align:"center", sortable: false},
	<?for($i = 0; $i < count(get_data('properties')); $i++):?>
		 {name:'property_<?=$i?>',index:'property_<?=$i?>'},
	<?endfor?>
		{name:'actions',index:'actions', align:"center", sortable: false},
	];

	var size = <?=count(get_data('found'))?>;
	$("#result-list").jqGrid({
		datatype: "local", 
		colNames: properties , 
		colModel: model, 
		width: parseInt($("#result-list").parent().width()) - 15, 
		sortname: 'id', 
		sortorder: "asc", 
		caption: __("Filter results")
	});
});
</script>
<?endif?>

<?include(dirname(__FILE__).'/../portail/footer.tpl');?>
