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
class tao_actions_Settings extends tao_actions_CommonModule {

	/**
	 * @access protected
	 * @var tao_models_classes_UserService
	 */
	protected $userService = null;
	
	/**
	 * initialize the services 
	 * @return 
	 */
	public function __construct(){
		$this->userService = tao_models_classes_UserService::singleton();
	}

	/**
	 * render the settings form
	 * @return void
	 */
	public function index(){
		
		$myFormContainer = new tao_actions_form_Settings($this->getLangs());
		$myForm = $myFormContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$currentUser = $this->userService->getCurrentUser();
				
				$uiLangCode 	= $myForm->getValue('ui_lang');
				$dataLangCode 	= $myForm->getValue('data_lang');
				
				$userSettings = array();
				
				$uiLangResource = tao_helpers_I18n::getLangResourceByCode($uiLangCode);
				if(!is_null($uiLangResource)){
					$userSettings[PROPERTY_USER_UILG] = $uiLangResource->uriResource;
				}
				$dataLangResource = tao_helpers_I18n::getLangResourceByCode($dataLangCode);
				if(!is_null($dataLangResource)){
					$userSettings[PROPERTY_USER_DEFLG] = $dataLangResource->uriResource;
				}
				
				if($this->userService->saveUser($currentUser, $userSettings)){
					
					tao_helpers_I18n::init($uiLangCode);
					
					core_kernel_classes_Session::singleton()->setLg($dataLangCode);
					
					$this->setData('message', __('settings updated'));
					
					$this->setData('reload', true);
				}
			}
		}
		$this->setData('myForm', $myForm->render());
                
                $optimizableClasses = $this->getOptimizableClasses();
                if(!empty($optimizableClasses)){
                        $this->setData('optimizable', true);
                }
                
		$this->setView('form/settings.tpl');
	}
	
	
	
	/**
	 * get the langage of the current user
	 * @return the lang codes
	 */
	private function getLangs(){
		
		$currentUser = $this->userService->getCurrentUser();
		
		$uiLang = $GLOBALS['default_lang'];
		$uiLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
		if(!is_null($uiLg) && $uiLg instanceof core_kernel_classes_Resource){
			$uiLang = $uiLg->getLabel();
		}
							
		$dataLang = $GLOBALS['default_lang'];
		$dataLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_DEFLG));
		if(!is_null($dataLg) && $dataLg instanceof core_kernel_classes_Resource){
			$dataLang = $dataLg->getLabel();
		}
		
                
		return array('data_lang' => $dataLang, 'ui_lang' => $uiLang);
	}
	
    /*
     * return a view the list of optimizable classes for the current extension
     */
    public function optimizeClasses(){
		
		$optimizableClasses = $this->getOptimizableClasses();

		$classes = array();
		$referencer = core_kernel_persistence_hardapi_ResourceReferencer::singleton();
		foreach($optimizableClasses as $optimizableClassUri => $options){
				$optimizableClass = new core_kernel_classes_Class($optimizableClassUri);
				$classes[] = array(
					'class'     =>      $optimizableClass->getLabel(),
					'classUri'  =>      $optimizableClassUri,
					'status'    =>      $referencer->isClassReferenced($optimizableClass)?__('compiled'):__('stand by'),
					'action'    => ''
				);
		}

		echo json_encode($classes);
    }
    
    /*
     * return a view the list of optimizable classes for the current extension
     */
    public function decompileClasses(){
		$optimizableClasses = $this->getOptimizableClasses();

		$classes = array();
		$referencer = core_kernel_persistence_hardapi_ResourceReferencer::singleton();
		foreach($optimizableClasses as $optimizableClassUri => $options){
				$optimizableClass = new core_kernel_classes_Class($optimizableClassUri);
				$classes[] = array(
					'class'     =>      $optimizableClass->getLabel(),
					'classUri'  =>      $optimizableClassUri,
					'status'    =>      $referencer->isClassReferenced($optimizableClass)?__('compiled'):__('uncompiled'),
					'action'    => ''
				);
		}


		echo json_encode($classes);
    }
    
    /*
     * get list of classes to be hardified
     * it need to be overwriten by inherited classes to give the right list of classes 
     */
    protected function getOptimizableClasses(){
            
		$returnValue = array();

		$optionsCompile = array(
				'recursive'             => true,
				'append'                => true,
				'createForeigns'        => true,
				'referencesAllTypes'	=> true,
				'rmSources'             => true
		);

		$optionsDecompile = array(
				'recursive'             => true,
				'removeForeigns'        => true				
		);

		$defaultOptions = array(
				'compile' => $optionsCompile,
				'decompile' => $optionsDecompile
			);

		$userClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/generis.rdf#User');
		$taoSubjectRoleClass =  new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#TaoSubjectRole');

		$returnValue = array(
				'http://www.tao.lu/Ontologies/TAO.rdf#Languages' => array(
						'compile' => array_merge($optionsCompile, array(
								'additionalProperties'  => array(new core_kernel_classes_Property('http://www.w3.org/1999/02/22-rdf-syntax-ns#value')),
								'createForeigns'        => false
								)
						),
						'decompile' => $optionsDecompile
					),
				'http://www.tao.lu/middleware/wfEngine.rdf#ClassSupportServices' => $defaultOptions,
				'http://www.tao.lu/middleware/wfEngine.rdf#ClassCallOfservicesResources' => $defaultOptions,
				'http://www.tao.lu/middleware/wfEngine.rdf#ClassServiceDefinitionResources' => $defaultOptions,
				'http://www.tao.lu/middleware/wfEngine.rdf#ClassServicesResources' => $defaultOptions,
				'http://www.tao.lu/middleware/wfEngine.rdf#ClassConnectors' => $defaultOptions,
				'http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessInstances' => $defaultOptions,
				'http://www.tao.lu/middleware/wfEngine.rdf#ClassActivityCardinality'=> $defaultOptions,
				'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject' => array(
						'compile' => array_merge(
							$optionsCompile, 
							array(
								'topClass' => $userClass, 
								'forceReferencing' => array($taoSubjectRoleClass)
								)
							),
						'decompile' => $optionsDecompile
					),
				'http://www.tao.lu/Ontologies/TAODelivery.rdf#Delivery' => $defaultOptions,
				'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group' => $defaultOptions,
				'http://www.tao.lu/Ontologies/TAODelivery.rdf#History' => array(
						'compile' => array_merge($optionsCompile, array('createForeigns' => false)),
						'decompile' => $optionsDecompile
				)    
		);

		return $returnValue;
    }
    
    /*
     * get list of properties to be indexes
     * it need to be overwriten by inherited classes to give the right list of classes 
     */
    protected function getOptimizableProperties(){
            
		$returnValue = array();

		$returnValue = array(
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersFormalParameter',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertySupportServicesUrl',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsType',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyConnectorsActivityReference',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesStatus',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyProcessInstancesExecutionOf',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsStatus',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsExecutionOf',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsCurrentUser',
			'http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsProcessExecution',
			'http://www.tao.lu/Ontologies/generis.rdf#login',
			'http://www.tao.lu/Ontologies/generis.rdf#password',
			'http://www.tao.lu/Ontologies/TAOResult.rdf#TAO_PROCESS_EXEC_ID',
			'http://www.tao.lu/Ontologies/TAOResult.rdf#TAO_DELIVERY_ID',
			'http://www.tao.lu/Ontologies/TAOResult.rdf#TAO_TEST_ID',
			'http://www.tao.lu/Ontologies/TAOResult.rdf#TAO_ITEM_ID',
			'http://www.tao.lu/Ontologies/TAOResult.rdf#TAO_SUBJECT_ID'
		);

		return $returnValue;
    }
    
    public function compileClass(){
            
		$result = array('success' => false);

		$class = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
		$optimizableClasses = $this->getOptimizableClasses();
		if(isset($optimizableClasses[$class->uriResource]) && isset($optimizableClasses[$class->uriResource]['compile'])){

			//build the option array and launch the compilation:
			$userDefinedOptions = array();
			$options = array_merge($optimizableClasses[$class->uriResource]['compile'], $userDefinedOptions);

			$switcher = new core_kernel_persistence_Switcher(array('http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessVariables'));
			$switcher->hardify($class, $options);

			//force referencing:
			if(isset($options['forceReferencing']) && is_array($options['forceReferencing'])){

				$resourceReferencer = core_kernel_persistence_hardapi_ResourceReferencer::singleton();

				foreach($options['forceReferencing'] as $additionalReferencingClass){
					if(!is_null($additionalReferencingClass) && $additionalReferencingClass instanceof core_kernel_classes_Class){
						if(!$resourceReferencer->isClassReferenced($additionalReferencingClass)){

							$referencingOptions = array('table' => '_'.core_kernel_persistence_hardapi_Utils::getShortName($class));
							if($options['topClass']){
								$referencingOptions['topClass'] = $options['topClass'];
							}

							$resourceReferencer->referenceClass($additionalReferencingClass, $referencingOptions);

						}
					}
				}
			}

			//prepare return value
			$hardenedClasses = $switcher->getHardenedClasses();
			$count = isset($hardenedClasses[$class->uriResource])?$hardenedClasses[$class->uriResource]:0;
			$relatedClasses = array();
			foreach($hardenedClasses as $relatedClassUri => $nb){
					if($relatedClassUri != $class->uriResource){
							$relatedClass = new core_kernel_classes_Class($relatedClassUri);
							$relatedClasses[$relatedClass->getLabel()] = $nb;
					}
			}

			$result = array(
				'success'    => true,
				'count'     => $count,
				'relatedClasses' => $relatedClasses
			);

			unset($switcher);
		}

		echo json_encode($result);
            
    }
    
    public function decompileClass(){
            
		$result = array('success' => false);

		$class = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
		$optimizableClasses = $this->getOptimizableClasses();
		if(isset($optimizableClasses[$class->uriResource]) && isset($optimizableClasses[$class->uriResource]['decompile'])){

			//build the option array and launch the compilation:
			$userDefinedOptions = array();
			$options = array_merge($optimizableClasses[$class->uriResource]['decompile'], $userDefinedOptions);

			$switcher = new core_kernel_persistence_Switcher(array('http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessVariables'));
			$switcher->unhardify($class, $options);


			//prepare return value
			$decompiledClass = $switcher->getDecompiledClasses();
			$count = isset($decompiledClass[$class->uriResource])?$decompiledClass[$class->uriResource]:0;
			$relatedClasses = array();
			foreach($decompiledClass as $relatedClassUri => $nb){
					if($relatedClassUri != $class->uriResource){
							$relatedClass = new core_kernel_classes_Class($relatedClassUri);
							$relatedClasses[$relatedClass->getLabel()] = $nb;
					}
			}

			$result = array(
				'success'    => true,
				'count'     => $count,
				'relatedClasses' => $relatedClasses
			);

			unset($switcher);
		}

		echo json_encode($result);
            
    }
    
    public function createPropertyIndex(){
            
		$properties = $this->getOptimizableProperties();
		$result = array(
			'success' => core_kernel_persistence_Switcher::createIndex($properties)
		);

		echo json_encode($result);
    }
	
}
?>