<?if(get_data('exit')):?>
	<script type="text/javascript">
		window.location = "<?=_url('index', 'Main', 'tao', array('structure' => 'users', 'message' => get_data('message')))?>";
	</script>
<?else:?>
	<script type="text/javascript" src='<?=TAOBASE_WWW?>js/users.js'></script>

	<?if(get_data('message')):?>
		<div id="info-box" class="ui-widget-header ui-corner-all auto-slide">
			<span><?=get_data('message')?></span>
		</div>
	<?endif?>
	<div class="main-container">
		<div class="data-container containerDisplay" id="lstroles">
			<span class="title"><?= __('Add roles') ?></span>
			<ul class="group-list"></ul>
		</div>

		<div class="main-container large">
			<div id="form-title" class="ui-widget-header ui-corner-top ui-state-default">
				<?=get_data('formTitle')?>
			</div>
			<div id="form-container" class="ui-widget-content ui-corner-bottom">
				<?=get_data('myForm')?>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		var ctx_extension 	= "<?=get_data('extension')?>";
		var ctx_module 		= "<?=get_data('module')?>";
		var ctx_action 		= "<?=get_data('action')?>";

		$(document).ready(function(){
			if(ctx_action == 'add'){
				uiBootstrap.tabs.tabs('disable', helpers.getTabIndexByName('edit_user'));
				checkLogin("<?=get_data('loginUri')?>", "<?=_url('checkLogin', 'Users', 'tao')?>");
			}

			$.ajax({
				type: "POST",
				url: "<?=_url('getRoles', 'Roles', 'tao')?>",
				data: 'useruri='+$('#uri').val(),
				dataType: 'json',
				success: function(data) {
					for (r in data) {
						extra = '';
						if (data[r].selected) extra = ' have-allaccess';
						html = '<li class="selectable'+extra+'" id="role_'+data[r].id+'"><ul class="actions"></ul><span class="label">'+data[r].label+'</span>';
						if (data[r].id == 'http://www.tao.lu/Ontologies/TAO.rdf#TaoManagerRole' && $('#uri').val().substring($('#uri').val().length-9) == 'superUser') {
							//nothing
						} else {
							html += '<span class="selector checkable" title="'+__('Add')+'"></span>';
						}
						html += '</li>';
						$el = $(html);
						$el.click(function(){
							if ($(this).hasClass('have-allaccess')) {
								unattachRole($(this).data('uri'));
								//$(this).removeClass('have-allaccess');
							} else {
								attachRole($(this).data('uri'));
								//$(this).addClass('have-allaccess');
							}
						});
						$el.data('uri', data[r].id);
						$el.appendTo($('#lstroles .group-list'));
					}
				}
			});
		});

		function attachRole(uri) {
			$.ajax({
				type: "POST",
				url: "<?=_url('attachRole', 'Roles', 'tao')?>",
				data: 'roleuri='+uri+'&useruri='+$('#uri').val(),
				dataType: 'json',
				success: function(data) {
					if (data.success) $('#role_'+data.id).addClass('have-allaccess');
				}
			});
		}

		function unattachRole(uri) {
			$.ajax({
				type: "POST",
				url: "<?=_url('unattachRole', 'Roles', 'tao')?>",
				data: 'roleuri='+uri+'&useruri='+$('#uri').val(),
				dataType: 'json',
				success: function(data) {
					if (data.success) $('#role_'+data.id).removeClass('have-allaccess');
				}
			});
		}
	</script>
<?endif?>