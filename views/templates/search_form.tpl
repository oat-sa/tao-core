<?include('header.tpl')?>

<?if(get_data('found')):?>
	<table id="result-list"></table>
	<div id="result-list-pager"></div> 
	<br />
<?endif?>

<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=get_data('formTitle')?>
</div>
<div id="form-container" class="ui-widget-content ui-corner-bottom">
	<?=get_data('myForm')?>
</div>

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
		{name:'id',index:'id', width:25, align:"center"},
	<?for($i = 0; $i < count(get_data('properties')); $i++):?>
		 {name:'property_<?=$i?>',index:'property_<?=$i?>'},
	<?endfor?>
		{name:'actions',index:'actions', width:150, align:"center", sortable: false},
	];
	
	$("#result-list").jqGrid({
		datatype: "local", 
		colNames: properties , 
		colModel: model, 
		rowNum: 20, 
		width: '100%', 
		pager: '#result-list-pager', 
		sortname: 'id', 
		viewrecords: false, 
		sortorder: "asc", 
		caption: __("Search results")
	});
	$("#result-list").jqGrid('navGrid','#result-list-pager',{edit:false, add:false, del:false});
	
	<?foreach(get_data('found') as $i => $row):?>
	
	jQuery("#result-list").jqGrid('addRowData', <?=$i?> , {
		'id' : <?=$i?>,
	<?foreach($row['properties'] as $j => $propValue):?>
		'property_<?=$j?>': '<?=$propValue?>',
	<?endforeach?>
		'actions': "<img class='icon' src='<?=TAOBASE_WWW?>/img/bullet_go.png' /><a href='#' onclick='GenerisAction.select(\"<?=$row['uri']?>\"); return false;' class='' ><?=__('Open')?></a>"
	}); 
	
	<?endforeach?>
});
</script>
<?endif?>

<?include('footer.tpl');?>
