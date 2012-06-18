<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Generis Extensions Manager</title>
		<style type="text/css">
			@import	url(<?php echo BASE_URL; ?><?php echo $GLOBALS['dir_theme'];?>css/tab.css);
		</style>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta http-equiv="Content-Language" content="en" />
		<meta name="MSSmartTagsPreventParsing" content="TRUE" />
		<meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />
		<meta name="GOOGLEBOT" content="NOSNIPPET" />
	</head>

	<body>
		<h1>Extension Manager</h1>
		<div id="message">
			<pre><?php if(isset($message)) echo $message; ?></pre>
		</div>
		<div id="availlableExtension">
		<fieldset>
		
			<legend>Available Extensions</legend>
			<table summary="modules" class="maximal">
				<thead>
					<tr>
						<th><?php echo __('Extension'); ?></th>
						<th class="nowrap"><?php echo __('Lastest Version'); ?></th>
						<th><?php echo __('Requires'); ?></th>
						<th><?php echo __('Author'); ?></th>
						<th><?php echo __('Actions'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($availlableExtArray as $k=>$ext): ?>
					<tr>
						<th><?php echo $ext->name; ?></th>
						<th class="nowrap"><?php echo $ext->version; ?></th>
						<th>
							<ul>
							<?php foreach ($ext->getDependencies() as $req):?>
								<li><?php echo $req ; ?></li>
								<?php endforeach;?>
							</ul>
						</th>
						<th><?php echo $ext->author; ?></th>
						<th>			
							<form action="<?php echo BASE_URL; ?>/ExtensionsManager/install" method="post">
							<input name="id" value="<?php echo $ext->getID();?>" type="hidden" /> 
							<input class="install" name="add_extension" value="<?php echo __('Install');?>" type="submit" />
							</form>
						</th>
					</tr>
					<?php endforeach;?>
				</tbody>	
			</table>
			</fieldset>
		</div>
		
		<div id="installedExtension">
			
			<fieldset>
			<legend>Installed Extensions</legend>
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
						<?php if($extensionObj->id !=null) :?>
						<tr>
							
							<th><?php echo $extensionObj->name; ?></th>
							<th class="nowrap"><?php echo $extensionObj->version; ?></th>
							<th><?php echo $extensionObj->author ; ?></th>
							<th>				
								
								<?php $loadedStr =  $extensionObj->configuration->loaded ? 'checked' : ''; ?>
							
								<input class="install" name="loaded[<?php echo $extensionObj->id; ?>]" type="checkbox" value='loaded' <?php echo $loadedStr; ?>  />
								
							</th>
							<th>				
								
								<?php $loadAtStartUpStr =  $extensionObj->configuration->loadedAtStartUp ? 'checked' : ''; ?>
								<input class="install" name="loadAtStartUp[<?php echo $extensionObj->id; ?>]" value='loadAtStartUp' type="checkbox" <?php echo $loadAtStartUpStr; ?>  />
								
							</th>
							<th>

							</th>
						</tr>
						<?php endif; ?>
					<?php endforeach;?>
				
					</tbody>
					

								
				</table>
				<p>
					<input class="save" name="save_extension" value="<?php echo __('Save');?>" type="submit" />
				</p>
				
				</form>
			</fieldset>
		</div>
	</body>
</html>
