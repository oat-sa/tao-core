<?php
/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class Main extends CommonModule {

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
	 * Login form
	 * @return 
	 */
	public function login(){
		
		if($this->getData('errorMessage')){
			session_destroy();
		}
		
		$myForm = tao_helpers_form_FormFactory::getForm('login', array('noRevert' => true, 'submitValue' => __('Connect')));
		$loginElt = tao_helpers_form_FormFactory::getElement('login', 'Textbox');
		$loginElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$myForm->addElement($loginElt);
		
		$passElt = tao_helpers_form_FormFactory::getElement('password', 'Hiddenbox');
		$passElt->addValidator(
			tao_helpers_form_FormFactory::getValidator('NotEmpty')
		);
		$myForm->addElement($passElt);
		
		$myForm->evaluate();
		
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
	 * 
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
		
		$this->setView('layout.tpl');
	}

	/**
	 * Load the actions for the current section and the current data context
	 * @return void
	 */
	public function getSectionActions(){
		
		$uri = $this->hasSessionAttribute('uri');
		$classUri = $this->hasSessionAttribute('classUri');
		
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
				array_push($actions, $action);
			}
			
			$this->setData('actions', $actions);
		}
		
		$this->setView('actions.tpl');
	}

	/**
	 * Load the section trees
	 * @return 
	 */
	public function getSectionTrees(){
		
		$this->setData('trees', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$structure = $this->service->getStructure($currentExtension, $this->getRequestParameter('section'));
			$this->setData('trees', $structure->trees[0]);
		}
		
		$this->setView('trees.tpl');
	}

}
?>