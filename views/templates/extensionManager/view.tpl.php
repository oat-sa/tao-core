<link rel="stylesheet" type="text/css" href="<?= TAOBASE_WWW ?>css/extensionManager.css" />
<script src="<?= TAOBASE_WWW ?>js/extensionManager.js"></script>

<? if(isset($message)): ?>
<div id="message">
	<pre><?= $message; ?></pre>
</div>
<? endif; ?>

<div id="extensions-manager-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?= __('Installed Extensions') ?>
</div>
<div id="extensions-manager-container" class="ui-widget-content ui-corner-bottom">
	<form action="<?= BASE_URL; ?>/ExtensionsManager/modify" method="post">
		<table summary="modules" class="maximal">
			<thead>
				<tr>
					<th><?= __('Extension'); ?></th>
					<th class="nowrap"><?= __('Latest Version'); ?></th>
					<th><?= __('Author'); ?></th>
					<th><?= __('Loaded'); ?></th>
					<th><?= __('Loaded at Startup'); ?></th>
				</tr>
			</thead>
			<tbody>
			<? foreach(get_data('installedExtArray') as $extensionObj): ?>
			<? if($extensionObj->id !=null): ?>
				<tr>
					<td class="ext-id"><?= $extensionObj->name; ?></td>
					<td class="nowrap"><?= $extensionObj->version; ?></td>
					<td><?= $extensionObj->author ; ?></td>
					<td><? $loadedStr =  $extensionObj->configuration->loaded ? 'checked' : ''; ?>
						<input class="install" name="loaded[<?= $extensionObj->id; ?>]" type="checkbox" value='loaded' <?= $loadedStr; ?>  />
					</td>
					<td><? $loadAtStartUpStr = $extensionObj->configuration->loadedAtStartUp ? 'checked' : ''; ?>
						<input class="install" name="loadAtStartUp[<?= $extensionObj->id; ?>]" value='loadAtStartUp' type="checkbox" <?= $loadAtStartUpStr; ?>  />
					</td>
				</tr>
			<? endif; ?>
			<? endforeach;?>
			</tbody>
		</table>
		<div class="actions">
			<input class="save" name="save_extension" value="<?= __('Save');?>" type="submit" />
		</div>
	</form>
</div>

<div id="available-extensions-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?= __('Available Extensions') ?>
</div>
<div id="available-extensions-container" class="ui-widget-content ui-corner-bottom">
	<form action="<?= BASE_URL; ?>/ExtensionsManager/install" metdod="post">
		<table summary="modules" class="maximal">
			<thead>
				<tr>
					<th><?= __('Install'); ?></th>
					<th><?= __('Extension'); ?></th>
					<th class="nowrap"><?= __('Latest Version'); ?></th>
					<th><?= __('Requires'); ?></th>
					<th><?= __('Author'); ?></th>
				</tr>
			</thead>
			<tbody>
				<? foreach(get_data('availableExtArray') as $k => $ext): ?>
				<tr id="<?= $ext->getID();?>">
					<td>
						<input name="ext_<?= $ext->getID();?>" type="checkbox" />
					</td>
					<td><?= $ext->name; ?></td>
					<td class="nowrap"><?= $ext->version; ?></td>
					<td class="dependencies">
						<ul>
						<? foreach ($ext->getDependencies() as $req): ?>
							<li class="ext-id ext-<?= $req ?><?= array_key_exists($req, get_data('installedExtArray')) ? ' installed' : '' ?>" rel="<?= $req ?>"><?= $req ?></li>
						<? endforeach; ?>
						</ul>
					</td>
					<td><?= $ext->author; ?></td>
				</tr>
				<? endforeach; ?>
			</tbody>
		</table>
		<div class="actions">
			<input class="install" name="install_extension" value="<?= __('Install') ?>" type="submit" />
		</div>
	</form>
</div>

<div id="installProgress" title="<?= __('Installation...') ?>">
	<div class="progress"><div class="bar"></div></div>
	<p class="status">...</p>
	<div class="console"></div>
</div>