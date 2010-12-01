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
$(document).ready(function(){

	//by changing the format, the form is sent
	$(":radio[name='format']").change(function(){
		$(this).parents('form').find('.form-submiter').click();
	});
	
	$("#files-list").jqGrid({
		url: "<?=_url('getExportedFiles', 'Export', 'tao')?>", 
		datatype: "json",
		colNames: [__('Name'),__('File'), __('Date'), __('Actions')] ,
		colModel: [
			{name:'name',index:'name',  align:"left"},
			{name:'path',index:'path',  align:"left"},
			{name:'date',index:'date',  align:"center"},
			{name:'actions',index:'actions', align:"center", sortable: false}
		], 
		rowNum: 20, 
		width: '', 
		pager: '#files-list-pager', 
		sortname: 'name', 
		viewrecords: false, 
		sortorder: "asc", 
		caption: __("Exported files library")
	});
	$("#files-list").jqGrid('navGrid','#files-list-pager',{edit:false, add:false, del:false});
	
});
</script>
<?include(TAO_TPL_PATH.'footer.tpl')?>