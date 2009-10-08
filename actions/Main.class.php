<?php
class Main extends Module {

	public function index(){
		$taoService = tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
		$extensions = array();
		foreach($taoService->getLoadedExtensions() as $extension){
			$extensions[$extension] = str_replace('tao', '', $extension);
		}
		$this->setData('extensions', $extensions);
		$this->setView('layout.tpl');
	}
}
?>