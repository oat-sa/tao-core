<?php
/**
 * Top level controller
 * All children extenions module should extends the CommonModule to access the shared data
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 */
abstract class CommonModule extends Module {

	/**
	 * The Modules access the models throught the service instance
	 * @var tao_models_classes_Service
	 */
	protected $service = null;
	
	/**
	 * Retrieve the data from the url and make the base initialization
	 * @return void
	 */
	protected function defaultData(){
		$context = Context::getInstance();
		if($this->hasSessionAttribute('currentExtension')){
			$this->setData('extension', $this->getSessionAttribute('currentExtension'));
			$this->setData('module',  $context->getModuleName());
			$this->setData('action',  $context->getActionName());
			
			if($this->getRequestParameter('showNodeUri')){
				$this->setSessionAttribute("showNodeUri", $this->getRequestParameter('showNodeUri'));
			}
			if($this->getRequestParameter('uri') || $this->getRequestParameter('classUri')){
				if($this->getRequestParameter('uri')){
					$this->setSessionAttribute('uri', $this->getRequestParameter('uri'));
				}
				else{
					unset($_SESSION[SESSION_NAMESPACE]['uri']);
				}
				if($this->getRequestParameter('classUri')){
					$this->setSessionAttribute('classUri', $this->getRequestParameter('classUri'));
				}
				else{
					unset($_SESSION[SESSION_NAMESPACE]['classUri']);
				}
			}
		}
		else{
			unset($_SESSION[SESSION_NAMESPACE]['uri']);
			unset($_SESSION[SESSION_NAMESPACE]['classUri']);
		}
		
		if($this->getRequestParameter('messsage')){
			$this->setData('messsage', $this->getRequestParameter('messsage'));
		}
		if($this->getRequestParameter('errorMessage')){
			$this->setData('errorMessage', $this->getRequestParameter('errorMessage'));
		}
	}

	/**
	 * Check if the current user is allowed to acces the request
	 * Override this method to allow/deny a request
	 * @return boolean
	 */
	protected function _isAllowed(){
		return tao_models_classes_UserService::isASessionOpened();	//if a user is logged in
	}
	
}
?>