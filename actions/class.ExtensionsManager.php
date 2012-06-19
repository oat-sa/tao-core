<?php

error_reporting(E_ALL);

/**
 * default action
 * must be in the actions folder
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package action
 */
class tao_actions_ExtensionsManager extends tao_actions_CommonModule {

	/**
	 * Index page
	 */
	public function index() {

		$extensionManager = common_ext_ExtensionsManager::singleton();
		$extensionManager->reset();
		$installedExtArray = $extensionManager->getInstalledExtensions();
		$availlableExtArray = $extensionManager->getAvailableExtensions();
		$this->setData('installedExtArray',$installedExtArray);
		$this->setData('availlableExtArray',$availlableExtArray);
		$this->setView('extensionManager/view.tpl.php');

	}

	protected function getExtension() {
		if ($this->hasRequestParameter('id')) {
			$extensionManager = common_ext_ExtensionsManager::singleton();
			return common_ext_ExtensionsManager::singleton()->getExtensionById($this->getRequestParameter('id'));
		} else {
			return null;
		}
	}

	public function add( $id , $package_zip ){

		$extensionManager = common_ext_ExtensionsManager::singleton();
		$fileUnzip = new fileUnzip(urldecode($package_zip));
		$fileUnzip->unzipAll(EXTENSION_PATH);
		$newExt = $extensionManager->getExtensionById($id);
		$extInstaller = new common_ext_ExtensionInstaller($newExt);
		try {
			$extInstaller->install();
			tao_helpers_funcACL_funcACL::removeRolesByActions();
			$message =   __('Extension ') . $newExt->name . __(' has been installed');
		}
		catch(common_ext_ExtensionException $e) {
			$message = $e->getMessage();
		}

		$this->setData('message',$message);
		$this->index();

	}

	public function install(){
		$success = false;
		try {
			$extInstaller = new common_ext_ExtensionInstaller($this->getExtension());
			$extInstaller->install();
			$message =   __('Extension ') . $this->getExtension()->getID() . __(' has been installed');
			$success = true;
		}
		catch(common_ext_ExtensionException $e) {
			$message = $e->getMessage();
		}

		echo json_encode(array('success' => $success, 'message' => $message));
	}


	public function modify($loaded,$loadAtStartUp){

		$extensionManager = common_ext_ExtensionsManager::singleton();
		$installedExtArray = $extensionManager->getInstalledExtensions();
		$configurationArray = array();
		foreach($installedExtArray as $k=>$ext){
			$configuration = new common_ext_ExtensionConfiguration(isset($loaded[$k]),isset($loadAtStartUp[$k]));
			$configurationArray[$k]=$configuration;
		}
		try {
			$extensionManager->modifyConfigurations($configurationArray);
			$message = __('Extensions\' configurations updated ');
		}
		catch(common_ext_ExtensionException $e) {
			$message = $e->getMessage();
		}
		$this->setData('message', $message);
		$this->index();

	}

}
?>