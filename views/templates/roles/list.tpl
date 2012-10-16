<script type="text/javascript" src="<?=BASE_WWW?>js/roles.js"></script>
<link rel="stylesheet" type="text/css" href="<?=BASE_WWW?>css/roles.css" />

<div class="main-container">
	<div class="actions">
		<span class="ui-state-default ui-corner-all" id="addrole"><a href="#"><img src="<?=TAOBASE_WWW?>img/add.png" alt="add" /> <?=__('Add a role')?></a></span>
	</div>

	<div class="containerDisplay" id="aclRoles">
		<span class="title"><?= __('Roles') ?></span>
		<form>
			<div>
				<select id="roles" name="roles" size="1">
					<option value=""><?= __('Roles') ?>...</option>
<?php foreach (get_data('roles') as $r): ?>
					<option value="<?= $r['id'] ?>"><?= $r['label'] ?></option>
<?php endforeach; ?>
				</select>
			</div>
		</form>
		<div class="actions" id="roleactions">
			<span class="ui-state-default ui-corner-all" id="editrole"><a href="#"><img src="<?=BASE_WWW?>img/edit.png" alt="edit" /> <?=__('Edit role')?></a></span>
			<span class="ui-state-default ui-corner-all" id="deleterole"><a href="#"><img src="<?=BASE_WWW?>img/delete.png" alt="delete" /> <?=__('Delete role')?></a></span>
		</div>
	</div>

	<div class="containerDisplay" id="aclModules">
		<span class="title"><?= __('Modules') ?></span>
		<ul class="group-list"></ul>
	</div>

	<div class="containerDisplay disabled" id="aclActions">
		<span class="title"><?= __('Actions') ?></span>
		<ul class="group-list"></ul>
	</div>

	<div class="containerDisplay disabled" id="aclRolesAffected">
		<span class="title"><?= __('Roles affected') ?></span>
		<ul class="group-list"></ul>
	</div>
</div>

<div id="addroleform" title="<?=__('Add a role')?>">
	<form method="post" action="<?=_url('addRole', 'Roles', 'tao')?>">
		<div><label for="addrole_name"><?= __('Name') ?></label> <input type="text" name="name" id="addrole_name" /></div>
	</form>
</div>

<div id="editroleform" title="<?=__('Edit a role')?>">
	<form method="post" action="<?=_url('editRole', 'Roles', 'tao')?>">
		<div><label for="editrole_name"><?= __('Name') ?></label> <input type="text" name="name" id="editrole_name" /></div>
	</form>
</div>