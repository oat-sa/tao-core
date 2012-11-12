<?php
/**
 * This controller provide the actions to manage the user settings
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * @subpackage action
 *
 */
class tao_actions_SettingsVersioning extends tao_actions_TaoModule {

	/**
	 * initialize the services
	 * @return
	 */
	public function __construct(){
		parent::__construct();
		$this->service = tao_models_classes_TaoService::singleton();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tao_actions_TaoModule::getRootClass()
	 */
	public function getRootClass() {
		return new core_kernel_classes_Class(CLASS_GENERIS_VERSIONEDREPOSITORY);
	}
	

	/**
	 * (non-PHPdoc)
	 * @see tao_actions_TaoModule::getCurrentInstance()
	 */
	public function getCurrentInstance() {
		$instance = parent::getCurrentInstance();
		return new core_kernel_versioning_Repository($instance);
	}
	
	/**
	 * render the settings form
	 * @return void
	 */
	public function index(){

		$this->defaultData();
		$this->setView('settings/versioningIndex.tpl');
		
	}

	/**
	 * (non-PHPdoc)
	 * @see tao_actions_TaoModule::addInstance()
	 */
	public function addInstance() {
		parent::addInstance();
	}
	
	/**
	 * render the repository form
	 * @return void
	 */
	public function editRepository() {
		//$myFormContainer = new tao_actions_form_Versioning();
		$clazz = $this->getCurrentClass();
		$repo = $this->getCurrentInstance();
		$myFormContainer = new tao_actions_form_Repository($clazz, $repo);
		
		$myForm = $myFormContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$values = $myForm->getValues();
				if (isset($values[PROPERTY_GENERIS_VERSIONEDREPOSITORY_ENABLED])) {
					unset($values[PROPERTY_GENERIS_VERSIONEDREPOSITORY_ENABLED]);
				}
				
				// save properties
				$repo = $this->service->bindProperties($repo, $values);
				$message = __('Repository saved');
				
				// check if enable/disable necessary
				$values = $myForm->getValues();
				if (isset($values[PROPERTY_GENERIS_VERSIONEDREPOSITORY_ENABLED])) {
					$oldState = $repo->getPropertyValues(new core_kernel_classes_Property(PROPERTY_GENERIS_VERSIONEDREPOSITORY_ENABLED));
					$oldState = count($oldState) == 1 ? current($oldState) : GENERIS_FALSE;
					$newState = $values[PROPERTY_GENERIS_VERSIONEDREPOSITORY_ENABLED];
					if ($newState == GENERIS_TRUE && $oldState != GENERIS_TRUE) {
						// enable the repository
						$success = $repo->enable();
						$message = $success ? __('Repository saved and enabled') : __('Repository saved, but unable to enable');
					} elseif ($newState != GENERIS_TRUE && $oldState == GENERIS_TRUE) {
						// disable the repository
						$success = $repo->disable();
						$message = $success ? __('Repository saved and disabled') : __('Repository saved, but unable to disable');
					} else {
						// nothing to do
					}
				}
				$this->setData('message',$message);
				$this->setData('reload', true);
			}
		}

		$this->setData('formTitle', __('Revision control'));
		$this->setData('myForm', $myForm->render());
		$this->setView('form.tpl', true);
	}

}
?>