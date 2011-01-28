<?php
/*
 * The generis extension loader is included there ONCE!
 *  1. Load and initialize the API and so the database
 *  2. Initialize the autoloaders
 *  3. Initialize the extension manager
 */
require_once dirname(__FILE__) . '/../../generis/common/inc.extension.php';
require_once DIR_CORE_HELPERS . 'Core.php';

/**
 * The Bootstrap Class enables you to drive the application flow for a given extenstion.
 * A bootstrap instance initialize the context and starts all the services:
 * 	- session
 *  - config
 *  - database
 *  - user
 *  - i18n
 * 
 * And it's used to disptach the Control Loop 
 * 
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage install
 * @example 
 * <code>
 *  $bootStrap = new BootStrap('tao');	//create the Bootstrap isntance
 *  $bootStrap->start();				//start all the services
 *  $bootStrap->dispatch();				//dispatch the http request into the control loop
 * </code>
 */
class Bootstrap{
	
	const SESSION_NAME = 'TAO_BCK_SESSION';
	
	/**
	 * @var string the contextual path
	 */
	protected $ctxPath = "";
	
	/**
	 * @var array misc options
	 */
	protected $options;
	
	/**
	 * @var boolean if the context has been started
	 */
	protected static $isStarted = false;
	
	/**
	 * @var boolean if the context has been dispatched
	 */
	protected static $isDispatched = false;
	
	/**
	 * @var common_ext_SimpleExtension
	 */
	protected $extension = null;
	
	/**
	 * Initialize the context
	 * @param string $extension
	 * @param array $options
	 */
	public function __construct($extension, $options = array()){
		
		$this->ctxPath = ROOT_PATH . '/' . $extension;
		
		if(PHP_SAPI == 'cli'){
			tao_helpers_Context::load('SCRIPT_MODE');		
		}
		else{
			tao_helpers_Context::load('APP_MODE');
		}
		
		$this->extension = new common_ext_SimpleExtension($extension);
		
		$this->options = $options;
	}
	
	/**
	 * Check if the current context has been started
	 * @return boolean
	 */
	public static function isStarted(){
		return self::$isStarted;
	} 
	
	/**
	 * Check if the current context has been dispatched
	 * @return boolean
	 */
	public static function isDispatched(){
		return self::$isDispatched;
	}
	
	/**
	 * Start all the services:
	 *  1. Start the session
	 *  2. Load the config
	 *  3. Update the include path
	 *  4. Include the global helpers
	 *  5. Connect the current user to the generis API
	 *  6. Initialize the internationalization
	 */
	public function start(){
		if(!self::$isStarted){
			$this->session();
			$this->config();
			$this->includePath();
			$this->globalHelpers();
			$this->connect();
			$this->i18n();
			self::$isStarted = true;
		}
	}
	
	/**
	 * Dispatch the current http request into the control loop:
	 *  1. Load the ressources
	 *  2. Start the MVC Loop from the ClearFW
	 */
	public function dispatch(){
		if(!self::$isDispatched){
			if(tao_helpers_Context::check('APP_MODE')){
				if(!tao_helpers_Request::isAjax()){
					$this->scripts();
				}
			}
			$this->mvc();
			self::$isDispatched = true;
		}
	}
	
	/**
	 * Start the session
	 */
	protected function session(){
		if(tao_helpers_Context::check('APP_MODE')){
			$request = new Request();
			if($request->hasParameter('session_id')){
			 	session_id($request->getParameter('session_id'));
			}
		}
		if(isset($this->options['session_name']) && !empty($this->options['session_name'])){
			session_name($this->options['session_name']);
		}
		else{
			session_name(self::SESSION_NAME);
		}
		
		session_start();
	}
	
	/**
	 * Load the config and constants
	 */
	protected function config(){
		
		//include the config file
		require_once $this->ctxPath. "/includes/config.php";
		
		//we will load the constant file of the current extension and all it's dependancies
		
		//get the dependancies
		$extensionManager = common_ext_ExtensionsManager::singleton();
		$extensions = $extensionManager->getDependancies($this->extension);
		
		//merge them with the additional constants (defined in the options)
		if(isset($this->options['constants'])){
			if(is_string($this->options['constants'])){
				$this->options['constants'] = array($this->options['constants']);
			}
			$extensions = array_merge($extensions, $this->options['constants']);
		}
		//add the current extension (as well !)
		$extensions = array_merge(array($this->extension->id), $extensions);
		
		foreach($extensions as $extension){
			
			if($extension == 'generis') continue; //generis constants are already loaded
			
			//loadt the config of the extension
			self::loadConstants($extension);
		}
	}
	
	/**
	 * Load the constant file of the extension
	 * @param string $extension
	 */
	public static function loadConstants($extension){
		$constantFile = ROOT_PATH . '/' . $extension . '/includes/constants.php';
		if(file_exists($constantFile)){
			
			//include the constant file
			include_once $constantFile;
			
			//this variable comes from the constant file and contain the const definition
			if(isset($todefine)){
				foreach($todefine as $constName => $constValue){
					if(!defined($constName)){
						define($constName, $constValue);	//constants are defined there!
					}
				}
				unset($todefine);
			}
		}
	}
	
	/**
	 * Update the include path
	 */
	protected function includePath(){
		set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH);
	}
	
	/**
	 * Include the global helpers 
	 * because of the shortcuts function like 
	 * _url() or _dh()  
	 * that are not loaded with the autoloader
	 */
	protected function globalHelpers(){
		require_once 'tao/helpers/class.Uri.php';
		require_once 'tao/helpers/class.Display.php';
	}
	
	/**
	 *  Start the MVC Loop from the ClearFW
	 *  @throws ActionEnforcingException in case of wrong module or action, send an HTTP CODE 404
	 *  @throws tao_models_classes_UserException when a request try to acces a protected area, it send and HTTP CODE 403
	 *  @throws Exception all exceptions not catched send an HTTP CODE 500
	 */
	protected function mvc(){
		
		try {
			$re		= new HttpRequest();
			$fc		= new AdvancedFC($re);
			$fc->loadModule();
		} 
		catch(ActionEnforcingException $ae){
			$message	= $ae->getMessage();
			if(DEBUG_MODE){
				$message .= "Called module :".$ae->getModuleName()."<br />";
				$message .= "Called action :".$ae->getActtionName()."<br />";
			}
			require_once TAO_TPL_PATH . 'error/error404.tpl';
		}
		catch(tao_models_classes_UserException $ue){
			$message	= $ue->getMessage();
			require_once TAO_TPL_PATH . 'error/error403.tpl';
		}
		catch (Exception $e) {
			$message	= $e->getMessage();
			if(DEBUG_MODE){
				$trace = $e->getTraceAsString();
			}
			require_once TAO_TPL_PATH . 'error/error500.tpl';
		}
	}
	
	/**
	 * Connect the current user to the generis API
	 * @see tao_models_classes_UserService::connectCurrentUser
	 */
	protected function connect(){
		$userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
		$userService->connectCurrentUser();
	}
	
	/**
	 * Initialize the internationalization
	 * @see tao_helpers_I18n
	 */
	protected function i18n(){
		$userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
		if(Session::hasAttribute('ui_lang')){
			$uiLang = Session::getAttribute('ui_lang') ;
		}
		else{
			$uiLg = null;
			$currentUser = $userService->getCurrentUser(); 
			if(!is_null($currentUser)){
				$uiLg  = $currentUser->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
			}
			(!is_null($uiLg)) ? $uiLang = $uiLg->getLabel() : $uiLang = $GLOBALS['default_lang'];
			
		}
		tao_helpers_I18n::init($uiLang);
		
		//only for legacy
		$GLOBALS['lang'] = $uiLang;
	}
	
	/**
	 * Load external resources for the current context
	 * @see tao_helpers_Scriptloader
	 */
	protected function scripts(){
		
		//stylesheets to load
		tao_helpers_Scriptloader::addCssFiles(array(
			TAOBASE_WWW . 'css/custom-theme/jquery-ui-1.8.custom.css',
			TAOBASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.css',
			TAOBASE_WWW . 'js/jquery.jqGrid-3.7.1/css/ui.jqgrid.css',
			TAOBASE_WWW . 'css/style.css',
			TAOBASE_WWW . 'css/layout.css',
			TAOBASE_WWW . 'css/form.css',
			TAOBASE_WWW . 'css/widgets.css'
		));
		
		
		//js golbal vars to export
		tao_helpers_Scriptloader::addJsVars(array(
			'root_url'		=> ROOT_URL,				// -> the app URL (http://www.domain.com or (http://www.domain.com/app)
			'base_url'		=> BASE_URL,				// -> the current extension URL (http://www.domain.com/tao, http://www.domain.com/taoItems)
			'taobase_www'	=> TAOBASE_WWW,				// -> the resources URL of meta extension tao (http://www.domain.com/tao/views/)
			'base_www'		=> BASE_WWW					// -> the resources URL of the current extension (http://www.domain.com/taoItems/views/)
		));
		
		$gridi18nFile = 'js/jquery.jqGrid-3.7.1/js/i18n/grid.locale-'.strtolower(tao_helpers_I18n::getLangCode()).'.js';
		if(!file_exists(BASE_PATH. '/views' . $gridi18nFile)){
			$gridi18nFile = 'js/jquery.jqGrid-3.7.1/js/i18n/grid.locale-en.js';
		}
		
		//scripts to load
		tao_helpers_Scriptloader::addJsFiles(array(
			TAOBASE_WWW . 'js/jquery-1.4.2.min.js',
			TAOBASE_WWW . 'js/jquery-ui-1.8.custom.min.js',
			TAOBASE_WWW . 'js/jsTree/jquery.tree.js',
			TAOBASE_WWW . 'js/jsTree/plugins/jquery.tree.contextmenu.js',
			TAOBASE_WWW . 'js/jsTree/plugins/jquery.tree.checkbox.js',
			TAOBASE_WWW . 'js/jwysiwyg/jquery.wysiwyg.js',
			TAOBASE_WWW . $gridi18nFile,
			TAOBASE_WWW . 'js/jquery.jqGrid-3.7.1/js/jquery.jqGrid.min.js',
			TAOBASE_WWW . 'js/jquery.numeric.js',
			ROOT_URL 	. '/filemanager/views/js/fmRunner.js',
			ROOT_URL 	. '/filemanager/views/js/jquery.fmRunner.js',
			TAOBASE_WWW . 'js/EventMgr.js',
			TAOBASE_WWW . 'js/gateway/Main.js',
			TAOBASE_WWW . 'js/helpers.js',
			TAOBASE_WWW . 'js/uiBootstrap.js',
			TAOBASE_WWW . 'js/uiForm.js',
			TAOBASE_WWW . 'js/generis.tree.js',
			TAOBASE_WWW . 'js/generis.actions.js',
			TAOBASE_WWW . 'js/generis.treeform.js',
			TAOBASE_WWW . 'js/AsyncFileUpload.js'
		));
		
		//ajax file upload works only without HTTP_AUTH
		if(!USE_HTTP_AUTH){
			tao_helpers_Scriptloader::addCssFile(
				TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/uploadify.css'
			);
			tao_helpers_Scriptloader::addJsFiles(array(
				TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/jquery.uploadify.v2.1.0.min.js',
				TAOBASE_WWW . 'js/jquery.uploadify-v2.1.0/swfobject.js'
			));
		}
	}
}
?>