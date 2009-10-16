<?php
class Main extends Module {

	public function index(){
		
		$taoService = tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
		$extensions = array();
		foreach($taoService->getStructure() as $extension => $xmlStruct){
			$extensions[$extension] = (string)$xmlStruct['name'];
		}
		$this->setData('extensions', $extensions);
		
		if($this->getRequestParameter('extension') != null){
			$taoService->setCurrentExtension($this->getRequestParameter('extension'));
		}
		
		$this->setData('sections', false);
		$currentExtension = $taoService->getCurrentExtension();
		if($currentExtension){
			$this->setData('sections', $taoService->getStructure($currentExtension)->sections[0]);
		}
		
		$this->setView('layout.tpl');
	}
	
	public function getSectionActions(){
		$taoService = tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
		$this->setData('actions', false);
		$currentExtension = $taoService->getCurrentExtension();
		if($currentExtension){
			$structure = $taoService->getStructure($currentExtension, $this->getRequestParameter('section'));
			$this->setData('actions', $structure->actions[0]);
		}
		
		$this->setView('actions.tpl');
	}
	
	public function logout(){
		session_destroy();
		$this->redirect('index');	
	}
}
?>