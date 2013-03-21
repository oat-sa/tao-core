<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
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
		parent::__construct();
		$this->userService = tao_models_classes_UserService::singleton();
	}

	/**
	 * render the settings form
	 * @return void
	 */
	public function index(){

		$optimizableClasses = $this->getOptimizableClasses();

		if(!empty($optimizableClasses)){
			$this->setData('optimizable', true);
		}
		$this->defaultData();

		$this->setView('form/settings_optimize.tpl');
	}



	/**
	 * get the langage of the current user
	 * @return the lang codes
	 */
	private function getLangs(){

		$currentUser = $this->userService->getCurrentUser();

		$uiLang = DEFAULT_LANG;
		$uiLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_UILG));
		if(!is_null($uiLg) && $uiLg instanceof core_kernel_classes_Resource){
			$uiLang = $uiLg->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE))->literal;
		}

		$dataLang = DEFAULT_LANG;
		$dataLg = $currentUser->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_USER_DEFLG));
		if(!is_null($dataLg) && $dataLg instanceof core_kernel_classes_Resource){
			$dataLang = $dataLg->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE))->literal;
		}

		$session = core_kernel_classes_Session::singleton();
		return array('data_lang' => $session->getDataLanguage(), 'ui_lang' => $session->getInterfaceLanguage());
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
				'createForeigns'        => false,
				'referencesAllTypes'	=> true,
				'rmSources'             => true
		);

		$optionsDecompile = array(
				'recursive'             => true,
				'removeForeigns'        => false
		);

		$defaultOptions = array(
				'compile' => $optionsCompile,
				'decompile' => $optionsDecompile
			);

		$optimizableClasses = array();
		$extManager = common_ext_ExtensionsManager::singleton();
		$extensions = $extManager->getInstalledExtensions();
		
		foreach ($extensions as $ext){
			$optimizableClasses = array_merge($optimizableClasses, $ext->getOptimizableClasses());
		}
		
		$optimizableClasses = array_unique($optimizableClasses);
		
		foreach ($optimizableClasses as $optClass){
			$returnValue[$optClass] = $defaultOptions;
		}

		return $returnValue;
    }

    protected function getOptimizableProperties(){

		$returnValue = array();

		$extManager = common_ext_ExtensionsManager::singleton();
		$extensions = $extManager->getInstalledExtensions();
		
		foreach ($extensions as $ext){
			$returnValue = array_merge($returnValue, $ext->getOptimizableProperties());
		}

		return $returnValue;
    }

    public function compileClass(){

		$result = array('success' => false);

		$class = new core_kernel_classes_Class(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
		$optimizableClasses = $this->getOptimizableClasses();
		if(isset($optimizableClasses[$class->uriResource]) && isset($optimizableClasses[$class->uriResource]['compile'])){

			//build the option array and launch the compilation:
			$options = array_merge($optimizableClasses[$class->uriResource]['compile']);

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