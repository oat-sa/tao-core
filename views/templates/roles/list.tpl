<script type="text/javascript" src="<?=BASE_WWW?>js/roles.js"></script>
<link rel="stylesheet" type="text/css" href="<?=BASE_WWW?>css/roles.css" />

<div class="main-container">
	<div class="actions">
		<span class="ui-state-default ui-corner-all"><a href="#"><img src="<?=BASE_WWW?>img/add.png" alt="add" /> <?=__('Add a role')?></a></span>
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
		<?= __('Parents') ?>
		<ul class="parents selectable multiple"></ul>
		<?= __('Children') ?>
		<ul class="children"></ul>
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