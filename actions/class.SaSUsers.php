<?php
/**
 * This controller provide the actions to manage the application users (list/add/edit/delete)
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_SaSUsers extends tao_actions_Users {

	/**
	 * @var tao_models_classes_UserService
	 */
	protected $userService = null;
	protected $userGridOptions = array();

	public function __construct() {
		tao_helpers_Context::load('STANDALONE_MODE');
		$this->setSessionAttribute('currentExtension', 'tao');
		parent::__construct();

		if ($this->hasRequestParameter('dataset')) {
			$dataset = $this->getRequestParameter('dataset');
		} else {
			$dataset = 'verbose';
		}
		//Base state
		$this->userGridOptions = array(
			'columns' => array(
				'country' => array('position' => 2, 'weight'=>0.5),
				'roles' => array('weight'=>2),
				PROPERTY_USER_UILG => array('weight'=>0.6),
				PROPERTY_USER_DEFLG => array('weight'=>0.6)
			),
			'excludedProperties' => array(
				PROPERTY_USER_UILG
			)
		);
		//Modified state
		if ($dataset == 'restricted') {
			$this->userGridOptions['excludedProperties'][] = RDFS_LABEL;
			$this->userGridOptions['excludedProperties'][] = PROPERTY_USER_LOGIN;
			$this->userGridOptions['excludedProperties'][] = PROPERTY_USER_DEFLG;
			$this->userGridOptions['excludedProperties'][] = 'country';
			unset($this->userGridOptions['columns']['country']);
			$this->userGridOptions['columns'][PROPERTY_USER_LASTNAME] = array('position' => 0);
		}
	}

	public function setView($identifier, $useMetaExtensionView = false) {
		if(tao_helpers_Request::isAjax()){
			return parent::setView($identifier, $useMetaExtensionView);
		}
    	if($useMetaExtensionView){
			$this->setData('includedView', $identifier);
		}
		else{
			$this->setData('includedView', DIR_VIEWS . $GLOBALS['dir_theme'] . $identifier);
		}
		return parent::setView('sas.tpl');
    }

	/**
	 * Grid display
	 */
	public function viewGrid(){
		$userGrid = new tao_models_grids_CustomUsers(array(), $this->userGridOptions);
		$model = $userGrid->getGrid()->getColumnsModel();
		$this->setData('model', json_encode($model));
		$this->setData('data', $userGrid->getGrid()->toArray());

		$this->setView('user/grid.tpl');
	}

	/**
	 * Get users data
	 */
	public function getGridData(){

		$returnValue = array();
		$filter = null;

		//get the filter
		if($this->hasRequestParameter('filter')){
			$filter = $this->getRequestParameter('filter');
			$filter = $filter == 'null' || empty($filter) ? null : $filter;
            if(is_array($filter)){
                foreach($filter as $propertyUri=>$propertyValues){
                    foreach($propertyValues as $i=>$propertyValue){
                        $propertyDecoded = tao_helpers_Uri::decode($propertyValue);
                        if(common_Utils::isUri($propertyDecoded)){
                            $filter[$propertyUri][$i] = $propertyDecoded;
                        }
                    }
                }
            }
		}
		//get the processes uris
		$usersUri = $this->hasRequestParameter('usersUri') ? $this->getRequestParameter('usersUri') : null;
		$users = array();
		$userClass = new core_kernel_classes_Class(CLASS_ROLE_TAOMANAGER);
		if(!is_null($filter)){
			$users = $userClass->searchInstances($filter, array ('recursive'=>true));
		}else if(!is_null($usersUri)){
			foreach($usersUri as $processUri){
				$users[$processUri] = new core_kernel_classes_resource($processUri);
			}
		}else{
			$users = $userClass->getInstances();
		}

		$userGrid = new tao_models_grids_CustomUsers(array_keys($users), $this->userGridOptions);
		$data = $userGrid->toArray();
		$returnValue = $data;

		echo json_encode($returnValue);
	}

}
?>