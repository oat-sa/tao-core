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
				'roles' => array('weight'=>2),
				PROPERTY_USER_UILG => array('weight'=>0.6),
				PROPERTY_USER_DEFLG => array('weight'=>0.6)
			),
			'excludedProperties' => array(
				PROPERTY_USER_UILG
			),
			'customProps' => array()
		);
		//Modified state
		if ($dataset == 'restricted') {
			$this->userGridOptions['excludedProperties'][] = RDFS_LABEL;
			$this->userGridOptions['excludedProperties'][] = PROPERTY_USER_LOGIN;
			$this->userGridOptions['excludedProperties'][] = PROPERTY_USER_DEFLG;
			$this->userGridOptions['columns'][PROPERTY_USER_LASTNAME] = array('position' => 0);
		}
		//Adding custom properties
		if ($this->hasRequestParameter('customprops') && strlen($this->getRequestParameter('customprops'))) {
			$customprops = explode(',', $this->getRequestParameter('customprops'));
			foreach ($customprops as $prop) {
				$this->userGridOptions['customProps'][trim($prop)] = array();
			}
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
			$this->setData('includedView', DIR_VIEWS . 'templates/' . $identifier);
		}
		return parent::setView('sas.tpl');
    }

	/**
	 * Grid display
	 */
	public function viewGrid() {
		if ($this->hasRequestParameter('cssurl')) {
			tao_helpers_Scriptloader::addCssFile($this->getRequestParameter('cssurl'));
		}

		$userGrid = new tao_models_grids_CustomUsers(array(), $this->userGridOptions);
		$model = $userGrid->getGrid()->getColumnsModel();
		$this->setData('model', json_encode($model));
		$this->setData('data', $userGrid->getGrid()->toArray());

		$gridParams = '?';
		if ($this->hasRequestParameter('customprops')) $gridParams .= 'customprops='.urlencode($this->getRequestParameter('customprops'));
		if ($this->hasRequestParameter('userClassUri')) $gridParams .= '&userClassUri='.urlencode($this->getRequestParameter('userClassUri'));
		if ($this->hasRequestParameter('filter')) $gridParams .= '&filter='.urlencode($this->getRequestParameter('filter'));
		$this->setData('gridParams', $gridParams);

		$this->setView('user/grid.tpl');
	}

	/**
	 * Get users data
	 */
	public function getGridData(){

		$returnValue = array();
		$filter = array(PROPERTY_USER_LOGIN => '*');

		//get the filter
		if($this->hasRequestParameter('filter')){
			$filterpar = $this->getRequestParameter('filter');
			$filterpar = $filterpar == 'null' || empty($filterpar) ? null : $filterpar;
            if(is_array($filterpar)){
                foreach($filterpar as $propertyUri=>$propertyValues){
                    foreach($propertyValues as $i=>$propertyValue){
                        $propertyDecoded = tao_helpers_Uri::decode($propertyValue);
                        if(common_Utils::isUri($propertyDecoded)){
                            $filter[$propertyUri][$i] = $propertyDecoded;
                        }
                    }
                }
            }
		}
		$userClassUri = ($this->hasRequestParameter('userClassUri') && strlen($this->getRequestParameter('userClassUri'))) ? $this->getRequestParameter('userClassUri') : CLASS_ROLE_TAOMANAGER;
		//get the processes uris
		$usersUri = $this->hasRequestParameter('usersUri') ? $this->getRequestParameter('usersUri') : null;
		$users = array();
		$userClass = new core_kernel_classes_Class($userClassUri);
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