<?php if(get_data('exit')):?>
	<script>
		window.location = "<?=_url('index', 'Main', 'tao', array('structure' => 'users', 'ext' => 'tao', 'message' => get_data('message')))?>";
	</script>
<?else:?>
	<?php if(get_data('message')):?>
		<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
			<span><?=get_data('message')?></span>
		</div>
	<?php endif?>
	
	<div class="main-container">
		<h2><?=get_data('formTitle')?></h2>
		<div class="form-container">
			<?=get_data('myForm')?>
		</div>
	</div>

	<script>
		
                require(['jquery', 'helpers', 'users'], function($, helpers, user){
                    var ctx_action  = "<?=get_data('action')?>";
                    var loginId     = "<?=get_data('loginUri')?>";
                    var url         = "<?=_url('checkLogin', 'Users', 'tao')?>";
                    if(ctx_action === 'add'){
                        $('#tabs').tabs('disable', helpers.getTabIndexByName('edit_user'));
                        user.checkLogin(loginId, url);
                    }
		});
	</script>
<?php endif?>