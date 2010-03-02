<?include('header.tpl')?>



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
	if(ctx_extension){
		url = '/' + ctx_extension + '/' + ctx_module + '/';
	}
	url += 'getExportedFiles';
	$("#files-list").jqGrid({
		url:url, 
		datatype: "json",
		colNames: [__('Name'),__('File'), __('Date'), __('Actions')] ,
		colModel: [
			{name:'name',index:'name', width:200, align:"left"},
			{name:'path',index:'path', width:350, align:"left"},
			{name:'date',index:'date', width:200, align:"center"},
			{name:'actions',index:'actions', width:300, align:"center", sortable: false}
		], 
		rowNum: 20, 
		width: '100%', 
		pager: '#files-list-pager', 
		sortname: 'name', 
		viewrecords: false, 
		sortorder: "asc", 
		caption: __("Exported files library")
	});
	$("#files-list").jqGrid('navGrid','#files-list-pager',{edit:false, add:false, del:false});
	
});
</script>


<?include('footer.tpl');?>
