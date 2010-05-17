<?if(get_data('exit')):?>
<script type="text/javascript">
	window.location = "<?=_url('index', 'Main', 'tao', array('extension' => 'users', 'message' => get_data('message')))?>";
</script>
<?elseif(get_data('message')):?>
<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
	<span><?=get_data('message')?></span>
</div>
<?endif?>
<div class="main-container">

	<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
		<?=get_data('formTitle')?>
	</div>
	<div id="form-container" class="ui-widget-content ui-corner-bottom">
		<?=get_data('myForm')?>
	</div>
</div>
<br />
	<span class="ui-widget ui-state-default ui-corner-all" style="padding:5px;">
		<a href="#" onclick="selectTabByName('list_users');"><?=__('Back')?></a>
	</span>
<script type="text/javascript">
var ctx_extension 	= "<?=get_data('extension')?>";
var ctx_module 		= "<?=get_data('module')?>";
var ctx_action 		= "<?=get_data('action')?>";
$(document).ready(function(){

	<?if(get_data('action') != 'edit'):?>
		UiBootstrap.tabs.tabs('disable', getTabIndexByName('edit_user'));
	<?endif?>

	//display the role dropdown if the wf user box is checked 
	$(".acls").click(function(){
		if($(this).val() == 'wf'){
			if($(this).attr('checked') == true){
				$(".wfRoles").attr('disabled', false);
			}
			else{
				$(".wfRoles").attr('disabled', true);
			}
		}
	});
	
});
</script>