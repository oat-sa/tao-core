<div class="main-container">
	<div id="list-container">
	
		<table>
		<?foreach(get_data('lists') as $i => $list):?>
			<?if($i==0 or $i%4==0):?><tr><?endif?>
			<td>
				<div id='list-data_<?=$list['uri']?>'>
					<fieldset>
						<legend><span><?=$list['label']?></span></legend>
						<div class="list-elements" id='list-elements_<?=$list['uri']?>'>
							<ol>
								<?foreach($list['elements'] as $level => $element):?>
									<li id="list-element_<?=$level?>"> 
										<span class="list-element" id="list-element_<?=$level?>_<?=$element['uri']?>" ><?=$element['label']?></span>
									</li>
								<?endforeach?>
							</ol>
						</div>
						<div class="list-controls">
						<?if($list['editable']):?>
							<a href="#" class="list-editor" id='list-editor_<?=$list['uri']?>'>
								<img src="<?=TAOBASE_WWW?>/img/pencil.png" class="icon" /><?=__('Edit')?>
							</a>
							|
							<a href="#" class="list-deletor" id='list-deletor_<?=$list['uri']?>'>
								<img src="<?=TAOBASE_WWW?>/img/delete.png" class="icon" /><?=__('Delete')?>
							</a>
						<?else:?>
							<?=__('Edit')?> | <?=__('Delete')?>
						
						<?endif?>
						</div>
					</fieldset>
				</div>
			</td>
			<?if($i>0 and $i%4==0):?></tr><?endif?>
		<?endforeach?>
		</table>
		<br />
		<div style="width:60%;margin:auto;">
			<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
				<strong><?=__('Create a list')?></strong>
			</div>
			<div id="form-container" class="ui-widget-content ui-corner-bottom">
				<?=get_data('form')?>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
$(document).ready(function(){

	var saveUrl = '/tao/Lists/saveLists';
	var delUrl = '/tao/Lists/saveLists';

	$(".list-editor").click(function(){
		uri = $(this).attr('id').replace('list-editor_', '');
		var listContainer = $("div[id='list-data_" + uri+"']");

		if(!listContainer.parent().is('form')){

			listContainer.wrap("<form />");
			listContainer.prepend("<input type='hidden' name='uri' value='"+uri+"' />");

			listContainer.find('legend span').replaceWith(function(){
				return "<input type='text' name='label' value='"+$(this).text()+" />";  
			});
	
			listContainer.find('.list-element').replaceWith(function(){
				return "<input type='text' name='" + $(this).attr('id') + "' value='"+$(this).text()+"' />";  
			});
			
			elementList = listContainer.find('ol');
			elementList.addClass('sortable-list');
			elementList.find('li').addClass('ui-state-default');
			elementList.find('li').prepend('<span class="ui-icon ui-icon-grip-dotted-vertical" ></span>');
			elementList.find('li').prepend('<span class="ui-icon ui-icon-arrowthick-2-n-s" ></span>');

			elementList.sortable({
				axis: 'y',
				opacity: 0.6,
				placeholder: 'ui-state-error',
				tolerance: 'pointer',
				update: function(event, ui){
					var map = {};
					$.each($(this).sortable('toArray'), function(index, id){
						map[id] = 'list-element_' + (index + 1);
					});
					$(this).find('li').each(function(){
						id = $(this).attr('id');
						if(map[id]){
							$(this).attr('id', map[id]);
							newName = $(this).find('input').attr('name').replace(id, map[id]);
							$(this).find('input').attr('name', newName);
						}
					});
				}
			});

			elementSaver = $("<a href='#'><img src='<?=TAOBASE_WWW?>img/save.png' class='icon' /><?=__('Save')?></a>");
			elementSaver.click(function(){
				$.postJson(
					saveUrl, 
					$(this).parents('form').serializeArray(), 
					function(response){
						if(response.saved){
							createInfoMessage(__("list saved"));
						}
					}
				);
			});
			elementList.after(elementSaver);

			elementList.after('<br />');

			elementAdder = $("<a href='#'><img src='<?=TAOBASE_WWW?>img/add.png' class='icon' /><?=__('New element')?></a>");
			elementAdder.click(function(){
				level = $(this).parent().find('ol').length + 1;
				$(this).parent().find('ol').append(
					"<li id='list-element_"+level+"' class='ui-state-default'>" +
						"<span class='ui-icon ui-icon-arrowthick-2-n-s' ></span>" +
						"<span class='ui-icon ui-icon-grip-dotted-vertical' ></span>" + 
						"<input type='text' name='list-element_"+level+"_' />" + 
					"</li>");
			});
			elementList.after(elementAdder);

		}
		
	});

	$(".list-deletor").click(function(){
		if(confirm(__("Please confirm you want to delete this list. This operation is not reversible."))){
			uri = $(this).attr('id').replace('list-editor_', '');
			$.postJson(
				delUrl,
				{uri: uri},
				function(response){
					if(response.deleted){
						createInfoMessage(__("list deleted"));
					}
				}
			);
		}
	});
});
</script>