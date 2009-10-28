<?php
class Main extends Module {

	protected $service = null;
	
	public function __construct(){
		$this->service = tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
	}

	public function index(){
		
		$extensions = array();
		foreach($this->service->getStructure() as $extension => $xmlStruct){
			$extensions[$extension] = (string)$xmlStruct['name'];
		}
		$this->setData('extensions', $extensions);
		
		if($this->getRequestParameter('extension') != null){
			$this->service->setCurrentExtension($this->getRequestParameter('extension'));
		}
		
		$this->setData('sections', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$this->setData('sections', $this->service->getStructure($currentExtension)->sections[0]);
		}
		
		$this->setView('layout.tpl');
	}
	
	public function getSectionActions(){
		
		$this->setData('actions', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$structure = $this->service->getStructure($currentExtension, $this->getRequestParameter('section'));
			$this->setData('actions', $structure->actions[0]);
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
	
	public function getSectionGrid(){
		$this->setData('grid', false);
		$currentExtension = $this->service->getCurrentExtension();
		if($currentExtension){
			$this->setData('grid', true);
		}
		
		$this->setView('grid.tpl');
	}
	
	public function logout(){
		session_destroy();
		$this->redirect('index');	
	}
}
?>