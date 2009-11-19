<?php
class Main extends CommonModule {

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
		$this->defaultData();
	}
	
	/**
	 * Login form
	 * @return 
	 */
	public function login(){
		
		$myForm = tao_helpers_form_FormFactory::getForm('login', array('noRevert' => true, 'submitValue' => __('Connect')));
		$loginElt = tao_helpers_form_FormFactory::getElement('login', 'Textbox');
		$loginElt->setLevel(1);
		$myForm->addElement($loginElt);
		
		$passElt = tao_helpers_form_FormFactory::getElement('password', 'Hiddenbox');
		$passElt->setLevel(2);
		$myForm->addElement($passElt);
		
		$myForm->evaluate();
		
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				$this->setSessionAttribute("auth_id", uniqid());
				$this->redirect('index');	
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
		$this->redirect(_url('login'));	
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
				$action = array(
					'js'		=> (isset($actionNode['js'])) ? (string)$actionNode['js'] : false,
					'url' 		=> (string)$actionNode['url'],
					'display'	=> (string)$actionNode['name'],
					'name'		=> _clean((string)$actionNode['name']),
					'uri'		=> ($uri) ? $this->getSessionAttribute('uri') : false,
					'classUri'	=> ($classUri) ? $this->getSessionAttribute('classUri') : false
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