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
function editUser(login){
	index = getTabIndexByName('edit_user');
	if(index && login){
		UiBootstrap.tabs.tabs('url', index, "/tao/Users/edit?login="+login);
		UiBootstrap.tabs.tabs('enable', index);
		selectTabByName('edit_user');
	}
}
$(function(){
	UiBootstrap.tabs.tabs('disable', getTabIndexByName('edit_user'));
	$("#user-list").jqGrid({
		url:'/tao/Users/data', 
		datatype: "json", 
		colNames:['', __('Login'), __('Name'), __('Email'), __('Company'), __('Permissions'), __('Data Language'), __('Interface Language'), __('Actions')], 
		colModel:[ 
			{name:'active',index:'active', width:25, align:"center", sortable: false},
			{name:'login',index:'login', width:150}, 
			{name:'name',index:'name', width:200}, 
			{name:'email',index:'email',  width:250}, 
			{name:'company',index:'company', width:150}, 
			{name:'acl',index:'acl', width:100, align:"left", sortable: false}, 
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
