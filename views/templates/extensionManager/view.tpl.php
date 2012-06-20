<link rel="stylesheet" type="text/css" href="<?=TAOBASE_WWW?>css/extensionManager.css" />
<script src="<?=TAOBASE_WWW?>js/extensionManager.js"></script>

<?php if(isset($message)): ?>
<div id="message">
	<pre> echo $message; ?></pre>
</div>
<?php endif; ?>

<div class="containerDisplay" id="installedExtension">
	<span class="title"><?= __('Installed Extensions') ?></span>
	<form action="<?php echo BASE_URL; ?>/ExtensionsManager/modify" method="post">
		<table summary="modules" class="maximal">
			<thead>
				<tr>
					<th><?php echo __('Extension'); ?></th>
					<th class="nowrap"><?php echo __('Lastest Version'); ?></th>
					<th><?php echo __('Author'); ?></th>
					<th><?php echo __('Loaded'); ?></th>
					<th><?php echo __('Loaded At Startup'); ?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach($installedExtArray as $extensionObj): ?>
<?php		if($extensionObj->id !=null): ?>
				<tr>
					<td class="ext-id"><?php echo $extensionObj->name; ?></td>
					<td class="nowrap"><?php echo $extensionObj->version; ?></td>
					<td><?php echo $extensionObj->author ; ?></td>
					<td><?php $loadedStr =  $extensionObj->configuration->loaded ? 'checked' : ''; ?>
						<input class="install" name="loaded[<?php echo $extensionObj->id; ?>]" type="checkbox" value='loaded' <?php echo $loadedStr; ?>  />
					</td>
					<td><?php $loadAtStartUpStr =  $extensionObj->configuration->loadedAtStartUp ? 'checked' : ''; ?>
						<input class="install" name="loadAtStartUp[<?php echo $extensionObj->id; ?>]" value='loadAtStartUp' type="checkbox" <?php echo $loadAtStartUpStr; ?>  />
					</td>
				</tr>
<?php		endif; ?>
<?php endforeach;?>
			</tbody>
		</table>
		<div class="actions">
			<input class="save" name="save_extension" value="<?php echo __('Save');?>" type="submit" />
		</div>
	</form>
</div>

<div class="containerDisplay" id="availlableExtension">
	<span class="title"><?= __('Available Extensions') ?></span>
	<form action="<?php echo BASE_URL; ?>/ExtensionsManager/install" metdod="post">
		<table summary="modules" class="maximal">
			<thead>
				<tr>
					<th><?php echo __('Install'); ?></th>
					<th><?php echo __('Extension'); ?></th>
					<th class="nowrap"><?php echo __('Lastest Version'); ?></th>
					<th><?php echo __('Requires'); ?></th>
					<th><?php echo __('Author'); ?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach($availlableExtArray as $k=>$ext): ?>
				<tr id="<?php echo $ext->getID();?>">
					<td>
						<input name="ext_<?php echo $ext->getID();?>" type="checkbox" />
					</td>
					<td><?php echo $ext->name; ?></td>
					<td class="nowrap"><?php echo $ext->version; ?></td>
					<td class="dependencies">
						<ul>
<?php		foreach ($ext->getDependencies() as $req): ?>
							<li class="ext-id ext-<?php echo $req ?>" rel="<?php echo $req ?>"><?php echo $req ; ?></li>
<?php		endforeach; ?>
						</ul>
					</td>
					<td><?php echo $ext->author; ?></td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
		<div class="actions">
			<input class="install" name="install_extension" value="<?php echo __('Install');?>" type="submit" />
		</div>
	</form>
</div>

<div id="installProgress" title="<?= __('Installation...') ?>">
	<div class="progress"><div class="bar"></div></div>
	<p class="status">...</p>
	<div class="console"></div>
</div>