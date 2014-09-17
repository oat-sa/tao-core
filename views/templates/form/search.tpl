<?php
use oat\tao\helpers\Template;

if(get_data('found')):?>
	<?if(get_data('foundNumber') > 0):?>
		<table id="result-list"></table>
		<div id="result-list-pager"></div> 
	<?else:?>
		<div class="ui-state-error"><?=__('No result found')?></div>
	<?endif?>
	<br />
<?endif?>

<div class="main-container">

    <h2><?=get_data('formTitle')?></h2>
    <div class="form-content">
        <?=get_data('myForm')?>
    </div>
</div>

<?if(get_data('found')):?>
<script>
require(['jquery', 'i18n', 'generis.actions', 'grid/tao.grid'], function($, __, generisActions) {
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
        var openAction = <?=get_data('openAction')?>;
        var $resultList = $("#result-list");
	$resultList.jqGrid({
		datatype: "local", 
		colNames: properties , 
		colModel: model, 
		width: parseInt($resultList.parent().width()) - 15, 
		sortname: 'id', 
		sortorder: "asc", 
		caption: __("Search results")
	});
	
	<?foreach(get_data('found') as $i => $row):?>
	
	$resultList.jqGrid('addRowData', <?=$i?> , {
		'id' : <?=$i?>,
		<?foreach($row['properties'] as $j => $propValue):?>
			'property_<?=$j?>': "<?=$propValue?>",
		<?endforeach?>
		'actions': "<img class='icon' src='<?=Template::img('img/bullet_go.png', 'tao')?>'/><a href='#' class='found-action' data-uri='<?=$row["uri"]?>'><?=__('Open')?></a>"
	}); 
	
	<?endforeach?>	
        $('.found-action', $resultList).click(function(e){
            e.preventDefault();
            if(typeof openAction === 'function'){
                openAction($(this).data('uri'));
            }
        });
});
</script>
<?php endif; ?>