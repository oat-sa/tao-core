<?if(get_data('message')):?>
<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
	<span><?=get_data('message')?></span>
</div>
<?endif?>
<div class="main-container"></div>
		<table id="user-list"></table>
		<div id="user-list-pager"></div> 
		
		<br />
		<span class="ui-state-default ui-corner-all"><a href="#" onclick="selectTabByName('add_user');"><img src="<?=BASE_WWW?>img/add.png" alt="add" /> <?=__('Add a user')?></a></span><br /><br />
		<span class="ui-state-default ui-corner-all"><a href="<?=_url('restore')?>"><img src="<?=BASE_WWW?>img/undo.png" alt="add" /> <?=__('Restore default user')?></a></span><br />

<script type="text/javascript">
function editUser(uri){
	index = getTabIndexByName('edit_user');
	if(index && login){
		editUrl = "<?=_url('edit', 'Users', 'tao')?>" + '?uri=' + uri;
		UiBootstrap.tabs.tabs('url', index, editUrl);
		UiBootstrap.tabs.tabs('enable', index);
		selectTabByName('edit_user');
	}
}
function removeUser(uri){
	if(confirm("<?=__('Please confirm user deletion')?>")){ 
		window.location = "<?=_url('delete', 'Users', 'tao')?>" + '?uri=' + uri;
	}
}
$(function(){
	UiBootstrap.tabs.tabs('disable', getTabIndexByName('edit_user'));
	$("#user-list").jqGrid({
		url: "<?=_url('data', 'Users', 'tao')?>", 
		datatype: "json", 
		colNames:[ __('Login'), __('Name'), __('Email'), __('Data Language'), __('Interface Language'), __('Actions')], 
		colModel:[ 
			{name:'login',index:'login', width:150}, 
			{name:'name',index:'name', width:200}, 
			{name:'email',index:'email',  width:250}, 
			{name:'deflg',index:'deflg', width:120, align:"center"},
			{name:'uilg',index:'uilg', width:120, align:"center"},
			{name:'actions',index:'actions', width:150, align:"center", sortable: false}
		], 
		rowNum:20, 
		width:'100%', 
		height:300, 
		pager: '#user-list-pager', 
		sortname: 'login', 
		viewrecords: false, 
		sortorder: "asc", 
		caption: __("Users")
	});
	$("#user-list").jqGrid('navGrid','#user-list-pager',{edit:false, add:false, del:false});
	_autoFx();
});
</script>
