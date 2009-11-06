<?php
class Main extends CommonModule {

	public function __construct(){
		$this->service = tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
		$this->defaultData();
	}

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
			$this->service->setCurrentExtension($this->getRequestParameter('extension'));
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
					'url' 		=> (string)$actionNode['url'],
					'display'	=> (string)$actionNode['name'],
					'name'		=> _clean((string)$actionNode['name'])
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

	public function getSectionTrees(){
		
		$this->setData('trees', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$structure = $this->service->getStructure($currentExtension, $this->getRequestParameter('section'));
			$this->setData('trees', $structure->trees[0]);
		}
		
		$this->setView('trees.tpl');
	}

	public function logout(){
		session_destroy();
		$this->redirect('index');	
	}
}
?>