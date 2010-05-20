<?if(get_data('message')):?>
	<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
		<span><?=get_data('message')?></span>
	</div>
<?endif?>
	<div class="main-container"></div>
	<table id="user-list"></table>
	<div id="user-list-pager"></div> 
	<br />
	<span class="ui-state-default ui-corner-all">
		<a href="#" onclick="selectTabByName('add_user');">
			<img src="<?=BASE_WWW?>img/add.png" alt="add" /> <?=__('Add a user')?>
		</a>
	</span>
	<br />
	<br />

<script type="text/javascript">
function editUser(uri){
	index = getTabIndexByName('edit_user');
	if(index && uri){
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
	var myGrid = $("#user-list").jqGrid({
		url: "<?=_url('data', 'Users', 'tao')?>", 
		datatype: "json", 
		colNames:[ __('Login'), __('Name'), __('Email'), __('Data Language'), __('Interface Language'), __('Actions')], 
		colModel:[ 
			{name:'login',index:'login'}, 
			{name:'name',index:'name'}, 
			{name:'email',index:'email', width: '200'}, 
			{name:'deflg',index:'deflg', align:"center"},
			{name:'uilg',index:'uilg', align:"center"},
			{name:'actions',index:'actions', align:"center", sortable: false}
		], 
		rowNum:20, 
		height:300, 
		width:'',
		pager: '#user-list-pager', 
		sortname: 'login', 
		viewrecords: false, 
		sortorder: "asc", 
		caption: __("Users"),
		gridComplete: function(){
			$.each(myGrid.getDataIDs(), function(index, elt){
				myGrid.setRowData(elt, {
					actions: "<a id='user_editor_"+elt+"' href='#' class='user_editor nd' ><img class='icon' src='<?=BASE_WWW?>img/pencil.png' alt='<?=__('Edit user')?>' /><?=__('Edit')?></a>&nbsp;|&nbsp;" +
					"<a id='user_deletor_"+elt+"' href='#' class='user_deletor nd' ><img class='icon' src='<?=BASE_WWW?>img/delete.png' alt='<?=__('Delete user')?>' /><?=__('Delete')?></a>"
				});
			});
			$(".user_editor").click(function(){
				editUser(this.id.replace('user_editor_', ''));
			});
			$(".user_deletor").click(function(){
				removeUser(this.id.replace('user_deletor_', ''));
			});
		}
	});
	myGrid.navGrid('#user-list-pager',{edit:false, add:false, del:false});
	
	_autoFx();
});
</script>