<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_Main extends tao_actions_CommonModule {

	/**
	 * @access protected
	 * @var tao_models_classes_UserService
	 */
	protected $userService = null;

	/**
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct()
	{
		//check if user is authenticated
		if(!$this->_isAllowed()){
        	$userUri = core_kernel_classes_Session::singleton()->getUserUri();
			if(!empty($userUri)){
				throw new tao_models_classes_UserException('Access Denied');
			} else {
				$params = tao_helpers_Request::isAjax()
					? array()
					: array('redirect' => _url(
						context::getInstance()->getActionName()
						,context::getInstance()->getModuleName()
						,context::getInstance()->getExtensionName()
					));
				$this->redirect(_url('login', 'Main', 'tao', $params));
			}
			return;
		}

		//initialize service
		$this->service = tao_models_classes_TaoService::singleton();
		$this->userService = tao_models_classes_UserService::singleton();
		$this->defaultData();

	}

	/**
	 * Authentication form,
	 * default page, main entry point to the user
	 * @return void
	 */
	public function login()
	{

		if($this->getData('errorMessage')){
			session_destroy();
		}

		if(!tao_helpers_Request::isAjax()){

			//add the login stylesheet
			tao_helpers_Scriptloader::addCssFile(TAOBASE_WWW . 'css/login.css');
			//tao_helpers_Scriptloader::addJsFile(BASE_WWW . 'js/login.js');

			$myLoginFormContainer = new tao_actions_form_Login($this->hasRequestParameter('redirect')
				? array('redirect' => $this->getRequestParameter('redirect'))
				: array()
			);
			$myForm = $myLoginFormContainer->getForm();

			if($myForm->isSubmited()){
				if($myForm->isValid()){
					if($this->userService->loginUser($myForm->getValue('login'), md5($myForm->getValue('password')))){
						if ($this->hasRequestParameter('redirect')) {
							$this->redirect($this->getRequestParameter('redirect'));
						} else {
							$this->redirect(_url('index', 'Main'));
						}
					}
					else{
						$this->setData('errorMessage', __('No account match the given login / password'));
					}
				}
			}

			$this->setData('form', $myForm->render());
			$this->setView('main/login.tpl');
		} else {
			if($this->hasRequestParameter('login') && $this->hasRequestParameter('password')){
				$returnValue = false;
				if ($this->userService->loginUser($this->getRequestParameter('login'), md5($this->getRequestParameter('password')))){
					$returnValue = true;
				}
				echo json_encode((object) array('success'=>$returnValue));
			}
		}

	}

	/**
	 * Logout, destroy the session and back to the login page
	 * @return
	 */
	public function logout()
	{
		session_destroy();
		$this->redirect(_url('login', 'Main', 'tao'));
	}

	/**
	 * The main action, load the layout
	 * @return void
	 */
	public function index()
	{
		$extensions = array();
		foreach ($this->service->getAllStructures() as $i => $structure) {
			if ($structure['data']['visible'] == 'true') {
				$data = $structure['data'];
				$extensions[$i] = array(
					'id'			=> (string) $structure['id'],
					'name' 			=> (string) $data['name'],
					'extension'		=> $structure['extension'],
					'description'	=> (string) $data->description
				);
				//					'enabled'		=> tao_helpers_funcACL_funcACL::hasAccess($structure['extension'], (string) basename(dirname($data['url'])), (string) basename($data['url']))
				//Test if access
				$access = false;
				foreach ($data->sections->section as $section) {
					list($ext, $mod, $act) = explode('/', trim((string) $section['url'], '/'));
					if (tao_helpers_funcACL_funcACL::hasAccess($ext, $mod, $act)) {
						$access = true;
						break;
					}
				}
				$extensions[$i]['enabled'] = $access;
			}
		}
		ksort($extensions);
		$this->setData('extensions', $extensions);

		if($this->hasRequestParameter('structure')) {
			// structured mode
			// @todo stop using session to manage uri/classUri
			$this->removeSessionAttribute('uri');
			$this->removeSessionAttribute('classUri');
			$this->removeSessionAttribute('showNodeUri');
			$structure = $this->service->getStructure($this->getRequestParameter('ext'), $this->getRequestParameter('structure'));

			$sections = array();
			foreach ($structure["data"]->sections[0] as $id => $section) {
				$url = explode('/', substr((string)$section['url'], 1));
				$ext = (isset($url[0])) ? $url[0] : null;
				$module = (isset($url[1])) ? $url[1] : null;
				$action = (isset($url[2])) ? $url[2] : null;

				if (tao_helpers_funcACL_funcACL::hasAccess($ext, $module, $action)) {
					$sections[] = array('id' => (string)$section['id'], 'url' => (string)$section['url'], 'name' => (string)$section['name']);
				}
			}

			if (count($sections) > 0) {
				$this->setData('sections', $sections);
				$this->setData('shownExtension', $this->getRequestParameter('ext'));
				$this->setData('shownStructure', $this->getRequestParameter('structure'));
			} else {
				common_Logger::w('no sections');
			}
		} else {
			// home screen
			$this->setData('sections', false);
			tao_helpers_Scriptloader::addCssFile(TAOBASE_WWW . 'css/home.css');
		}

		$this->setData('user_lang', core_kernel_classes_Session::singleton()->getDataLanguage());

		$this->setView('layout.tpl', true);
	}

    /**
     * Check if the system is ready
     */
    public function isReady(){
		if(tao_helpers_Request::isAjax()){
            // the default ajax response is successfull style rastafarai
            $ajaxResponse = new common_AjaxResponse();
        }
        else{
            throw new common_exception_IsAjaxAction(__CLASS__.'::'.__METHOD__.'()');
        }
    }

	/**
	 * Load the actions for the current section and the current data context
	 * @return void
	 */
	public function getSectionActions()
	{

		$uri = $this->hasSessionAttribute('uri');
		$classUri = $this->hasSessionAttribute('classUri');
		$extname = $this->hasRequestParameter('ext');
		$struct = $this->getRequestParameter('structure');

		$rootClasses = array(TAO_GROUP_CLASS, TAO_ITEM_CLASS, TAO_RESULT_CLASS, TAO_SUBJECT_CLASS, TAO_TEST_CLASS);

		$this->setData('actions', false);
		$this->setData('shownExtension', $this->getRequestParameter('ext'));

		$structure = $this->service->getSection($extname, $struct, $this->getRequestParameter('section'));
		if(isset($structure["actions"])){
			$actionNodes =  $structure["actions"];
			$actions = array();
			foreach($actionNodes as $actionNode){
				$nocheck = true;
				if (isset($actionNode['url']) && strlen((string)$actionNode['url']) > 0) {
					$url = explode('/', substr((string)$actionNode['url'], 1));
					if (count($url) == 3) {
						$ext = (isset($url[0])) ? $url[0] : null;
						$module = (isset($url[1])) ? $url[1] : null;
						$action = (isset($url[2])) ? $url[2] : null;
						$nocheck = false;
					}
				}

				if ($nocheck || tao_helpers_funcACL_funcACL::hasAccess($ext, $module, $action)) {
					$display = __((string) $actionNode['name']);
					if(strlen($display) > 15){
						$display = str_replace(' ', "<br>", $display);
					}
					$action = array(
						'js'		=> (isset($actionNode['js'])) ? (string) $actionNode['js'] : false,
						'url' 		=> ROOT_URL . substr((string)$actionNode['url'], 1),
						'display'	=> $display,
						'rowName'	=> (string) $actionNode['name'],
						'name'		=> _clean((string) $actionNode['name']),
						'uri'		=> ($uri) ? $this->getSessionAttribute('uri') : false,
						'classUri'	=> ($classUri) ? $this->getSessionAttribute('classUri') : false,
						'reload'	=> (isset($actionNode['reload'])) ? true : false
					);

					$action['disabled'] = true;
					switch((string) $actionNode['context']){
						case 'resource':
							if($classUri || $uri) {
								$action['disabled'] = false; break;
							}
							break;
						case 'class':
							if($classUri && !$uri) {
								$action['disabled'] = false; break;
							}
							break;
						case 'instance':
							if($classUri && $uri) {
								$action['disabled'] = false; break;
							}
							break;
						case '*': $action['disabled'] = false; break;
						default : $action['disabled'] = true; break;
					}

					//@todo remove this when permissions engine is setup
					if($action['rowName'] == 'delete' && $classUri && !$uri){
						if(in_array($action['classUri'], tao_helpers_Uri::encodeArray($rootClasses, tao_helpers_Uri::ENCODE_ARRAY_VALUES))){
							$action['disabled'] = true;
						}
					}

					array_push($actions, $action);
				}
			}

			$this->setData('actions', $actions);
		}

		$this->setView('main/actions.tpl', true);
	}

	/**
	 * Load the filter section
	 * @return html
	 */
	public function getSectionFilters()
	{
		$this->setData('filters', false);
		$currentExtension = context::getInstance()->getExtensionName();
		if($currentExtension){
			//Filter by query
			//Filter by text
			//Filter by facet

		}
		$this->setView('main/filters.tpl');
	}

	/**
	 * Load the section trees
	 * @return void
	 */
	public function getSectionTrees()
	{

		$this->setData('trees', false);
		$extname = $this->getRequestParameter('ext');
		$struct = $this->getRequestParameter('structure');

		$structure = $this->service->getSection($extname, $struct, $this->getRequestParameter('section'));
		if(isset($structure["trees"])){
			$trees = array();
			foreach($structure["trees"] as $tree){
			    $treeArray = array();
				foreach($tree->attributes() as $attrName => $attrValue){
					if(preg_match("/^\//", (string) $attrValue)){
						$treeArray[$attrName] = ROOT_URL . (string)$attrValue;
					}
					else{
						$treeArray[$attrName] = (string)$attrValue;
					}
				}
				$treeId = tao_helpers_Display::textCleaner((string) $tree['name'], '_');
				$trees[$treeId] = $treeArray;
			}
			$this->setData('trees', $trees);

			$openUri = false;
			if($this->hasSessionAttribute("showNodeUri")){
				$openUri = $this->getSessionAttribute("showNodeUri");
			}
			$this->setData('openUri', $openUri);

			//differentiate the instanceName of Deliveries and Process definition from the others
			if($extname=="taoDelivery" || $extname=="wfEngine"){
				$this->setData('instanceName', $this->getSessionAttribute('currentSection'));
			}else{
				$this->setData('instanceName', strtolower(str_replace('tao', '', substr($extname, 0, strlen($extname) - 1))));
			}
		}

		$this->setView('main/trees.tpl', true);
	}

}
?>