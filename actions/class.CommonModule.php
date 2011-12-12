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
abstract class tao_actions_CommonModule extends Module {

	/**
	 * The Modules access the models throught the service instance
	 * @var tao_models_classes_Service
	 */
	protected $service = null;
	
	/**
	 * constructor checks if a user is logged in
	 * If you don't want this check, please override the  _isAllowed method to return true
	 */
	public function __construct()
	{
		if(!$this->_isAllowed()){
			throw new tao_models_classes_UserException(__('Access denied. Please renew your authentication!'));
		}
	}
	
	/**
     * @see Module::setView()
     * @param string $identifier view identifier
     * @param boolean set to true if you want to use the views in the tao extension instead of the current extension 
     */
    public function setView($identifier, $useMetaExtensionView = false)
    {
        parent::setView($identifier);
		if($useMetaExtensionView){
			Renderer::setViewsBasePath(TAOVIEW_PATH);
		}
		return;
	}
	
	/**
	 * Retrieve the data from the url and make the base initialization
	 * @return void
	 */
	protected function defaultData()
	{
		$context = Context::getInstance();
		if($this->hasSessionAttribute('currentExtension')){
			$this->setData('extension', $this->getSessionAttribute('currentExtension'));
			$this->setData('module', $context->getModuleName());
			$this->setData('action', $context->getActionName());
			
			if($this->getRequestParameter('showNodeUri')){
				$this->setSessionAttribute("showNodeUri", $this->getRequestParameter('showNodeUri'));
			}
			if($this->getRequestParameter('uri') || $this->getRequestParameter('classUri')){
				if($this->getRequestParameter('uri')){
					if ($this->getRequestParameter('uri') != 'undefined')
						$this->setSessionAttribute('uri', $this->getRequestParameter('uri'));
					else
						common_Logger::w('parameter uri was send but undefined');
				}
				else{
					$this->removeSessionAttribute('uri');
				}
				if($this->getRequestParameter('classUri')){
					$this->setSessionAttribute('classUri', $this->getRequestParameter('classUri'));
				}
				else{
					$this->removeSessionAttribute('classUri');
				}
			}
		}
		else{
			$this->removeSessionAttribute('uri');
			$this->removeSessionAttribute('classUri');
		}
		
		if($this->getRequestParameter('message')){
			$this->setData('message', $this->getRequestParameter('message'));
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
	protected function _isAllowed()
	{
		//if a user is logged in
		return core_kernel_users_Service::singleton()->isASessionOpened();
	}
	
}
?>