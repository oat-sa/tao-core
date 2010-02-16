<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class Main extends CommonModule {

	/**
	 * @access protected
	 * @var tao_models_classes_UserService
	 */
	protected $userService = null;

	/**
	 * Constructor performs initializations actions
	 * @return void
	 */
	public function __construct(){
		
		//check if user is authenticated
		$context = Context::getInstance();
		if(!$this->_isAllowed() &&  $context->getActionName() != 'login'){
			$this->logout();
			return;
		}
		
		//initialize service
		$this->service = tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
		$this->userService = tao_models_classes_ServiceFactory::get('tao_models_classes_UserService');
		$this->defaultData();
	}
	
	/**
	 * Authentication form, 
	 * default page, main entry point to the user
	 * @return void
	 */
	public function login(){
		
		if($this->getData('errorMessage')){
			session_destroy();
		}
		
		$myLoginFormContainer = new tao_actions_form_Login();
		$myForm = $myLoginFormContainer->getForm();
		
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				if($this->userService->loginUser($myForm->getValue('login'), $myForm->getValue('password'))){
					$this->redirect(_url('index', 'Main'));	
				}
				else{
					$this->setData('errorMessage', __('No account match the given login / password'));
				}
			}
		}
		
		$this->setData('form', $myForm->render());
		$this->setView('login.tpl');
	}

	/**
	 * Logout, destroy the session and back to the login page
	 * @return 
	 */
	public function logout(){
		session_destroy();
		$this->redirect(_url('login', 'Main'));	
	}

	/**
	 * The main action, load the layout
	 * @return void
	 */
	public function index(){
		
		$extensions = array();
		foreach($this->service->getStructure() as $i => $structure){
			$extensions[$i] = array(
				'name' 		=> (string)$structure['data']['name'],
				'extension'	=> $structure['extension']
			);
		}
		ksort($extensions);
		$this->setData('extensions', $extensions);
		
		if($this->getRequestParameter('extension') != null){
			if($this->getRequestParameter('extension') == 'none'){
				unset($_SESSION[SESSION_NAMESPACE]['currentExtension']);
			}
			else{
				$this->service->setCurrentExtension($this->getRequestParameter('extension'));
			}
			unset($_SESSION[SESSION_NAMESPACE]['uri']);
			unset($_SESSION[SESSION_NAMESPACE]['classUri']);
		}
		
		$this->setData('sections', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$this->setData('sections', $this->service->getStructure($currentExtension)->sections[0]);
		}
		$this->setData('currentExtension', $currentExtension);
		
		$user = $this->userService->getCurrentUser();
		if(isset($user['login'])){
			$this->setData('user_lang', $this->userService->getUserLanguage($user['login']));
		}
		else{
			$this->setData('user_lang', $this->userService->getDefaultLanguage());
		}
		
		$this->setView('layout.tpl');
	}

	/**
	 * Load the actions for the current section and the current data context
	 * @return void
	 */
	public function getSectionActions(){
		
		$uri = $this->hasSessionAttribute('uri');
		$classUri = $this->hasSessionAttribute('classUri');
		
		$rootClasses = array(TAO_GROUP_CLASS, TAO_ITEM_CLASS, TAO_RESULT_CLASS, TAO_SUBJECT_CLASS, TAO_TEST_CLASS);
		
		$this->setData('actions', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$structure = $this->service->getStructure($currentExtension, $this->getRequestParameter('section'));
			$actionNodes =  $structure->actions[0];
			$actions = array();
			foreach($actionNodes as $actionNode){
				$display = (string)$actionNode['name'];
				if(strlen($display) > 15){
					$display = str_replace(' ', "<br>", $display);
				} 
				$action = array(
					'js'		=> (isset($actionNode['js'])) ? (string)$actionNode['js'] : false,
					'url' 		=> (string)$actionNode['url'],
					'display'	=> $display,
					'rowName'	=> (string)$actionNode['name'],
					'name'		=> _clean((string)$actionNode['name']),
					'uri'		=> ($uri) ? $this->getSessionAttribute('uri') : false,
					'classUri'	=> ($classUri) ? $this->getSessionAttribute('classUri') : false,
					'reload'	=> (isset($actionNode['reload'])) ? true : false
				);
				
				$action['disabled'] = true;
				switch((string)$actionNode['context']){
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
					if(in_array($action['classUri'], array_map("tao_helpers_Uri::encode", $rootClasses))){
						$action['disabled'] = true; 
					}
				}
				
				array_push($actions, $action);
			}
			
			$this->setData('actions', $actions);
		}
		
		$this->setView('actions.tpl');
	}

	/**
	 * Load the section trees
	 * @return void
	 */
	public function getSectionTrees(){
		
		$this->setData('trees', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
		
			$structure = $this->service->getStructure($currentExtension, $this->getRequestParameter('section'));
			$this->setData('trees', $structure->trees[0]);
			
			$openUri = false;
			if($this->hasSessionAttribute("showNodeUri")){
				$openUri = $this->getSessionAttribute("showNodeUri");
				unset($_SESSION[SESSION_NAMESPACE]["showNodeUri"]);
			}
			$this->setData('openUri', $openUri);
			
			//differentiate the instanceName of Deliveries from the others
			if($currentExtension=="taoDelivery"){
				$this->setData('instanceName', $this->getSessionAttribute('currentSection'));
			}else{
				$this->setData('instanceName', strtolower(str_replace('tao', '', substr($currentExtension, 0, strlen($currentExtension) - 1))));
			}
		}
		
		$this->setView('trees.tpl');
	}

}
?>