<?php 
// md5 is used to create a hash of the tree id, because uri are not valid XML ID
?>

<?include(dirname(__FILE__).'/../header.tpl')?>

<link rel="stylesheet" type="text/css" href="<?=BASE_WWW?>css/form_delivery.css" />
   
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
		if(ctx_extension){
			url = root_url + '/' + ctx_extension + '/' + ctx_module + '/';
		}
		getUrl = url + 'getBrol';
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
			//Refresh all with the new filter
			for (var treeId in filterTrees){
				filterTrees[treeId].setServerParameter('filter', filter, true);
			}
		});
	});
	</script>
		
</div>

<?include(dirname(__FILE__).'/../footer.tpl');?>