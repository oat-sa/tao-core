<?php
require_once dirname(__FILE__) . '/../../generis/common/inc.extension.php';
require_once DIR_CORE_HELPERS . 'Core.php';

class Bootstrap{
	
	protected $ctxPath = "";
	
	protected static $isStarted = false;
	protected static $isDispatched = false;
	
	public function __construct($extension){
		$this->ctxPath = ROOT_PATH . '/' . $extension;
		if(PHP_SAPI == 'cli'){
			tao_helpers_Context::load('SCRIPT_MODE');
		}
		else{
			tao_helpers_Context::load('APP_MODE');
		}
	}
	
	public static function isStarted(){
		return self::$isStarted;
	} 
	
	public static function isDispatched(){
		return self::$isDispatched;
	}
	
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
	
	protected function session(){
		if(tao_helpers_Context::check('APP_MODE')){
			$request = new Request();
			if($request->hasParameter('session_id')){
			 	session_id($request->getParameter('session_id'));
			}
		}
		session_start();
	}
	
	protected function config(){
		require_once $this->ctxPath. "/includes/config.php";
		require_once $this->ctxPath. "/includes/constants.php";
	}
	
	protected function includePath(){
		set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH);
	}
	
	protected function globalHelpers(){
		require_once 'tao/helpers/class.Uri.php';
		require_once 'tao/helpers/class.Display.php';
	}
	
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
			require_once TAOVIEW_PATH . $GLOBALS['dir_theme'] . 'error404.tpl';
		}
		catch(ActionEnforcingException $ae){
			$message	= $ae->getMessage();
			if(DEBUG_MODE){
				$message .= "Called module :".$ae->getModuleName()."<br />";
				$message .= "Called action :".$ae->getActtionName()."<br />";
			}
			require_once TAOVIEW_PATH . $GLOBALS['dir_theme'] . 'error404.tpl';
		}
		catch (Exception $e) {
			$message	= $e->getMessage();
			if(DEBUG_MODE){
				$message .= "<pre>".$e->getTraceAsString()."</pre>";
			}
			require_once TAOVIEW_PATH . $GLOBALS['dir_theme'] . 'error404.tpl';
		}
	}
	
	protected function connect(){
		$userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
		$userService->connectCurrentUser();
	}
	
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