<?include(TAO_TPL_PATH.'header.tpl')?>

<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=get_data('formTitle')?>
</div>
<div id="form-container" class="ui-widget-content ui-corner-bottom">
	<?=get_data('myForm')?>
</div>
<br />
<table id="files-list"></table>
<div id="files-list-pager"></div>
<br />
<script type="text/javascript">
$(function(){
	require(['require', 'jquery', 'grid/tao.grid.downloadFileResource', 'grid/tao.grid.rowId'], function(req, $) {
		//by changing the format, the form is sent
		$(":radio[name='format']").change(function(){
			var form = $(this).parents('form');
			$(":input[name='"+form.attr('name')+"_sent']").remove();
			form.find('.form-submiter').click();
		});

		var myGrid = $("#files-list").jqGrid({
			url: root_url + '/' + currentExtension + '/Export/getExportedFiles',
			datatype: "json",
			colNames: [__('Name'),__('File'), __('Date'), __('Actions')] ,
			colModel: [
				{name:'name',index:'name',  align:"left"},
				{name:'path',index:'path',  align:"left"},
				{name:'date',index:'date',  align:"center"},
				{name:'actions',index:'actions', align:"center", sortable: false}
			],
			rowNum: 20,
			width: parseInt($("#files-list").width()) - 5,
			pager: '#files-list-pager',
			sortname: 'name',
			viewrecords: false,
			sortorder: "asc",
			caption: __("Exported files library"),
			gridComplete: function(){
				$.each(myGrid.getDataIDs(), function(index, elt){
					var rowData = myGrid.getRowData(elt);
					var actionsUris = rowData.actions.split(',');
					var actions = $('<span></span>');
					var separator = '&nbsp;|&nbsp;';

					if(! /\.zip$/.test(actionsUris[0])){
						var viewAction = $("<a class='nd' target='_blank' ><img src='"+taobase_www+"img/search.png' class='icon'  title='"+__('View') +"' />" +__('View') + "</a>");
						viewAction.attr('href', actionsUris[0]);
						actions.append(viewAction);

						actions.append(separator);
					}
					var downloadAction = $("<a class='nd'><img src='"+taobase_www+"img/bullet_go.png' class='icon'  title='"+__('download')+"' />"+__('Download')+"</a>");
					downloadAction.attr('href', actionsUris[1]);
					actions.append(downloadAction);

					actions.append(separator);

					var deleteAction = $("<a class='nd export-deleter'><img src='"+taobase_www+"img/delete.png' class='icon'  title='"+__('delete')+"' />"+__('Delete')+"</a>");
					deleteAction.attr('href', actionsUris[2]);
					actions.append(deleteAction);

					myGrid.setRowData(elt, {actions: actions.html()});
				});

				$("#files-list td[aria-describedby='files-list_actions']").attr('title', __('actions'));
				$("a.export-deleter").click(function(){

					$.ajax({
						url: $(this).attr('href'),
						dataType: 'json',
						success: function(response){
							if(response.deleted){
								myGrid.trigger("reloadGrid");
								helpers.createInfoMessage(__("File deleted successfully"));
							}
						}
					});

					return false;
				});
				$(window).unbind('resize').bind('resize', function(){
					myGrid.jqGrid('setGridWidth', parseInt($("#files-list").width()) - 5);
				});
			}
		});

		$("#files-list").jqGrid('navGrid','#files-list-pager',{edit:false, add:false, del:false});
	});
});
</script>
<?include(TAO_TPL_PATH.'footer.tpl')?>