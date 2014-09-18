<?php 
use oat\tao\helpers\Template;
?>
<div class="data-container-wrapper standalone">
	<table id="user-list"></table>
</div>
<script type="text/javascript">
require(['jquery', 'i18n', 'helpers', 'ui/usermgr'], function($, __, helpers) {
	var $tabs = $('#tabs');
        
    var editUser = function editUser(uri) {
	    var index = helpers.getTabIndexByName('edit_user');
	    if (index && uri) {
		    var editUrl = "<?=_url('edit', 'Users', 'tao')?>" + '?uri=' + uri;
		    $tabs.tabs('url', index, editUrl);
		    $tabs.tabs('enable', index);
		    helpers.selectTabByName('edit_user');
	    }
    }
        
	var removeUser = function removeUser(uri){
		if (confirm("<?=__('Please confirm user deletion')?>")) {
			window.location = "<?=_url('delete', 'Users', 'tao')?>" + '?uri=' + uri;
		}
	};
        
    $('#user-list').usermgr({
        'url': '<?=_url('data', 'Users', 'tao')?>',
        'edit': editUser,
        'remove': removeUser
    });
});
</script>
