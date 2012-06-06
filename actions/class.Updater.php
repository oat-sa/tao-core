<?php

class tao_actions_Updater extends tao_actions_CommonModule {
	
	public function __construct()
	{
		parent::__construct();
		$this->updater = new tao_update_Updator();
	}
	
	/*
	public function update()
	{
		if($this->hasRequestParameter('version')){
			$version = $this->getRequestParameter('version');
			try {
				$returnValue = $this->updater->update($version);
				$updated = true;
			} catch (Exception $e){
				$updated = false;
			}
		}
		$returnValue = Array (
			"updated"=>$updated,
			"ouput"=>$this->updater->getOutput()
		);
		echo json_encode($returnValue);
	}
	*/
	
	public function checkUpdate()
	{
		$returnValue = Array(
			'updatable'=>$this->updater->checkUpdate()
		);
		echo json_encode((object) $returnValue);
	}
	
	public function getUpdatesDetails() 
	{
		$returnValue = $this->updater->getUpdatesDetails();
		echo json_encode($returnValue);
	}
	
}
?>
