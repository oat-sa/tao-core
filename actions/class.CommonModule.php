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
		
		$this->setData('extension', context::getInstance()->getExtensionName());
		$this->setData('module', $context->getModuleName());
		$this->setData('action', $context->getActionName());
		
		if($this->hasRequestParameter('uri')) {
			
			// @todo stop using session to manage uri/classUri
			$this->setSessionAttribute('uri', $this->getRequestParameter('uri'));
			
			// inform the client of new classUri
			$this->setData('uri', $this->getRequestParameter('uri'));
		}
		if($this->hasRequestParameter('classUri')) {
		
			// @todo stop using session to manage uri/classUri
			$this->setSessionAttribute('classUri', $this->getRequestParameter('classUri'));
			if (!$this->hasRequestParameter('uri')) {
				$this->removeSessionAttribute('uri');
			}
			
			// inform the client of new classUri
			$this->setData('uri', $this->getRequestParameter('classUri'));
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