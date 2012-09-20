<?php

foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $extension) {
	$finalFile	= $extension->getDir().'/includes/config.php';
	$sampleFile	= $extension->getDir().'/includes/config.php.sample';
	
	if (file_exists($finalFile) && !file_exists($sampleFile)) {
		unlink($finalFile);
	}
}

?>