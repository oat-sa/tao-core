<?php

error_reporting(E_ALL);

/**
 * This container initialize the export form.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * This class provide a container for a specific form instance.
 * It's subclasses instanciate a form and it's elements to be used as a
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.FormContainer.php');

/* user defined includes */
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-includes begin
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-includes end

/* user defined constants */
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-constants begin
// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED4-constants end

/**
 * This container initialize the export form.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Export
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute formats
     *
     * @access protected
     * @var array
     */
    protected $formats = array('rdf' => 'RDF');

    // --- OPERATIONS ---

    /**
     * Short description of method initForm
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED5 begin
		
    	$this->form = new tao_helpers_form_xhtml_Form('export');
				
		$this->form->setDecorators(array(
			'element'			=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')),
			'group'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')),
			'error'				=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all')),
			'actions-bottom'	=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar')),
			'actions-top'		=> new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-toolbar'))
		));
				
		
    	$exportElt = tao_helpers_form_FormFactory::getElement('export', 'Free');
		$exportElt->setValue( "<a href='#' class='form-submiter' ><img src='".TAOBASE_WWW."/img/export.png' /> ".__('Export')."</a>");
		
		$this->form->setActions(array($exportElt), 'bottom');
		// section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED5 end
    }

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED7 begin
		
    	//create the element to select the import format
    	$formatElt = tao_helpers_form_FormFactory::getElement('format', 'Radiobox');
    	$formatElt->setDescription(__('Please select the way to export the data'));
    	
    	//mandatory field
    	$formatElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
    	$formatElt->setOptions($this->formats);

    	//shortcut: add the default value here to load the first time the form is defined
		if(count($this->formats) == 1){
			foreach($this->formats as $formatKey => $format){
				$formatElt->setValue($formatKey);
			}
		}
		if(isset($_POST['format'])){
			if(array_key_exists($_POST['format'], $this->formats)){
				$formatElt->setValue($_POST['format']);
			}
		}
		
    	$this->form->addElement($formatElt);
    	$this->form->createGroup('formats', __('Supported export formats'), array('format'));
    	
    	//load dynamically the method regarding the selected format 
    	if(!is_null($this->form->getValue('format')) && strlen($this->form->getValue('format')) > 0){
    		$method = "init".strtoupper($this->form->getValue('format'))."Elements";
    		
    		if(method_exists($this, $method)){
    			$this->$method();
    		} else {
    			common_Logger::w('Methode \''.$method.'\' not found for the export format \''.$this->form->getValue('format').'\'', array('TAO'));
    		}
    	}
		
    	if(isset($this->data['instance'])){
    		$item = $this->data['instance'];
    		if($item instanceof core_kernel_classes_Resource){
				//add an hidden elt for the instance Uri
				$uriElt = tao_helpers_form_FormFactory::getElement('uri', 'Hidden');
				$uriElt->setValue($item->uriResource);
				$this->form->addElement($uriElt);
    		}	
    	}
    	if(isset($this->data['class'])){
    		$class = $this->data['class'];
    		if($class instanceof core_kernel_classes_Class){
    			//add an hidden elt for the class uri
				$classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
				$classUriElt->setValue($class->uriResource);
				$this->form->addElement($classUriElt);
    		}	
    	}
    	
        // section 127-0-1-1-74d22378:1271a9c9d21:-8000:0000000000001ED7 end
    }

    /**
     * Short description of method initRDFElements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initRDFElements()
    {
        // section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000291A begin
        
    	(isset($this->data['currentExtension'])) ? $fileName = $this->data['currentExtension'] : $fileName = '';
    	$instances = array();
    	if(isset($this->data['instance'])){
    		$instance = $this->data['instance'];
    		if($instance instanceof core_kernel_classes_Resource){
    			$fileName = strtolower(tao_helpers_Display::textCleaner($instance->getLabel()));
    			$instances[$instance->uriResource] = $instance->getLabel();
    		}
    	}
    	else {
    		if(isset($this->data['class'])){
	    		$class = $this->data['class'];
	    		if($class instanceof core_kernel_classes_Class){
					$fileName =  strtolower(tao_helpers_Display::textCleaner($class->getLabel()));
					foreach($class->getInstances() as $instance){
						$instances[$instance->uriResource] = $instance->getLabel();
					}
	    		}
    		}
    	}
    	$instances = tao_helpers_Uri::encodeArray($instances, tao_helpers_Uri::ENCODE_ARRAY_KEYS);
    	
    	$descElt = tao_helpers_form_FormFactory::getElement('rdf_desc', 'Label');
		$descElt->setValue(__('Enables you to export an RDF file containing the selected namespaces or instances'));
		$this->form->addElement($descElt);
		
		$nameElt = tao_helpers_form_FormFactory::getElement('filename', 'Textbox');
		$nameElt->setDescription(__('File name'));
		$nameElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$nameElt->setValue($fileName);
		$nameElt->setUnit(".rdf");
		$this->form->addElement($nameElt);
		
		//get the current Namespaces and dependancies
		$currentNs = array();
		if( isset($this->data['currentExtension'])){
			$currentExtentsion = new common_ext_SimpleExtension($this->data['currentExtension']);
			$currentNs =  $currentExtentsion->model;
			
			$extManager = common_ext_ExtensionsManager::singleton();
			foreach($extManager->getDependancies($currentExtentsion) as $dependancy){
				$ext = new common_ext_SimpleExtension($dependancy);
				$currentNs =  array_merge($currentNs, $ext->model);
			}
		}
		
		$nsManager = common_ext_NamespaceManager::singleton();
		
		$tplElt = new tao_helpers_form_elements_template_Template('rdftpl');
		$tplElt->setPath(TAO_TPL_PATH . '/form/rdfexport.tpl.php');
		$tplElt->setVariables(array(
			'namespaces' 	=> $nsManager->getAllNamespaces(),
			'localNs'		=> $nsManager->getLocalNamespace()->getModelId(),
			'currentNs'		=> $currentNs,
			'instances'		=> $instances
		));
		$this->form->addElement($tplElt);
    	
		
		$this->form->createGroup('options', __('Export Options'), array('rdf_desc', 'filename', 'rdftpl'));
    	
        // section 127-0-1-1-70b2308e:12ca2398ae8:-8000:000000000000291A end
    }

} /* end of class tao_actions_form_Export */

?>