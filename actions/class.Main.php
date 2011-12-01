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
		$context = Context::getInstance();
		if(!$this->_isAllowed() &&  $context->getActionName() != 'login'){
			$this->logout();
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
			tao_helpers_Scriptloader::addJsFile(BASE_WWW . 'js/login.js');
			
			$myLoginFormContainer = new tao_actions_form_Login();
			$myForm = $myLoginFormContainer->getForm();
			
			if($myForm->isSubmited()){
				if($myForm->isValid()){
					if($this->userService->loginUser($myForm->getValue('login'), md5($myForm->getValue('password')))){
						$this->redirect(_url('index', 'Main'));	
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
		$this->redirect(_url('login', 'Main'));	
	}

	/**
	 * The main action, load the layout
	 * @return void
	 */
	public function index()
	{
		$extensions = array();
		foreach($this->service->getStructure() as $i => $structure){
			if($structure['extension'] != 'users'){
				$data = $structure['data'];
				$extensions[$i] = array(
					'name' 			=> (string) $data['name'],
					'extension'		=> $structure['extension'],
					'description'	=> (string) $data->description
				);
			}
		}
		ksort($extensions);
		$this->setData('extensions', $extensions);
		
		if($this->getRequestParameter('extension') != null){
			if($this->getRequestParameter('extension') == 'none'){
				$this->removeSessionAttribute('currentExtension');
			}
			else{
				$this->service->setCurrentExtension($this->getRequestParameter('extension'));
			}
			$this->removeSessionAttribute('uri');
			$this->removeSessionAttribute('classUri');
			$this->removeSessionAttribute('showNodeUri');
		}
		
		$this->setData('sections', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$this->setData('sections', $this->service->getStructure($currentExtension)->sections[0]);
		}
		else{
			//add home page stylesheet
			tao_helpers_Scriptloader::addCssFile(TAOBASE_WWW . 'css/home.css');
		}
		$this->setData('currentExtension', $currentExtension);
		
		$this->setData('user_lang', core_kernel_classes_Session::singleton()->getLg());
		
		$this->setView('layout.tpl');
	}

	/**
	 * Load the actions for the current section and the current data context
	 * @return void
	 */
	public function getSectionActions()
	{
		
		$uri = $this->hasSessionAttribute('uri');
		$classUri = $this->hasSessionAttribute('classUri');
		
		$rootClasses = array(TAO_GROUP_CLASS, TAO_ITEM_CLASS, TAO_RESULT_CLASS, TAO_SUBJECT_CLASS, TAO_TEST_CLASS);
		
		$this->setData('actions', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$structure = $this->service->getStructure($currentExtension, $this->getRequestParameter('section'));
			
			if(isset($structure->actions[0])){
				$actionNodes =  $structure->actions[0];
				$actions = array();
				foreach($actionNodes as $actionNode){
					$display = __((string) $actionNode['name']);
					if(strlen($display) > 15){
						$display = str_replace(' ', "<br>", $display);
					} 
					$action = array(
						'js'		=> (isset($actionNode['js'])) ? (string) $actionNode['js'] : false,
						'url' 		=> ROOT_URL.(string) $actionNode['url'],
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
				
				$this->setData('actions', $actions);
			}
		}
		
		$this->setView('main/actions.tpl');
	}

	/**
	 * Load the filter section
	 * @return html
	 */
	public function getSectionFilters()
	{
		$this->setData('filters', false);
		$currentExtension = $this->service->getCurrentExtension();
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
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
		
			$structure = $this->service->getStructure($currentExtension, $this->getRequestParameter('section'));
			if(isset($structure->trees[0])){
				$trees = array();
				foreach($structure->trees[0] as $tree){
					foreach($tree->attributes() as $attrName => $attrValue){
						if(preg_match("/^\//", (string) $attrValue)){
							$tree[$attrName] = ROOT_URL.(string) $attrValue;
						}
						else{
							$tree[$attrName] = (string) $attrValue;
						}
					}
					$treeId = tao_helpers_Display::textCleaner((string) $tree['name'], '_');
					$trees[$treeId] = $tree;
				}
				$this->setData('trees', $trees);
				
				$openUri = false;
				if($this->hasSessionAttribute("showNodeUri")){
					$openUri = $this->getSessionAttribute("showNodeUri");
				}
				$this->setData('openUri', $openUri);
				
				//differentiate the instanceName of Deliveries and Process definition from the others
				if($currentExtension=="taoDelivery" || $currentExtension=="wfEngine"){
					$this->setData('instanceName', $this->getSessionAttribute('currentSection'));
				}else{
					$this->setData('instanceName', strtolower(str_replace('tao', '', substr($currentExtension, 0, strlen($currentExtension) - 1))));
				}
			}
		}
		
		$this->setView('main/trees.tpl');
	}

}
?>