<?php

error_reporting(E_ALL);

/**
 * TAO - tao/actions/form/class.VersionedFile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 21.10.2011, 16:55:38 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
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
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
require_once('tao/helpers/form/class.FormContainer.php');

/* user defined includes */
// section 127-0-1-1-234d8e6a:13300ee5308:-8000:0000000000003F7C-includes begin
// section 127-0-1-1-234d8e6a:13300ee5308:-8000:0000000000003F7C-includes end

/* user defined constants */
// section 127-0-1-1-234d8e6a:13300ee5308:-8000:0000000000003F7C-constants begin
// section 127-0-1-1-234d8e6a:13300ee5308:-8000:0000000000003F7C-constants end

/**
 * Short description of class tao_actions_form_VersionedFile
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_VersionedFile
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method initForm
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    public function initForm()
    {
        // section 127-0-1-1-234d8e6a:13300ee5308:-8000:0000000000003F7E begin
        
    	$this->form = tao_helpers_form_FormFactory::getForm('versioned_file');
    	
    	$actions = tao_helpers_form_FormFactory::getCommonActions();
    	$this->form->setActions($actions, 'top');
    	$this->form->setActions($actions, 'bottom');
    	
        // section 127-0-1-1-234d8e6a:13300ee5308:-8000:0000000000003F7E end
    }

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    public function initElements()
    {
        // section 127-0-1-1-234d8e6a:13300ee5308:-8000:0000000000003F80 begin
    
    	if(!isset($this->options['instanceUri'])){
    		throw new Exception(__('Option instanceUri is not an option !!'));
    	}
    	if(!isset($this->options['ownerUri'])){
    		throw new Exception(__('Option ownerUri is not an option !!'));
    	}
    	if(!isset($this->options['propertyUri'])){
    		throw new Exception(__('Option propertyUri is not an option !!'));
    	}
    	
    	$ownerInstance = new core_kernel_versioning_File($this->options['ownerUri']);
    	$property = new core_kernel_classes_Property($this->options['propertyUri']);
    	$instance = new core_kernel_versioning_File($this->options['instanceUri']);
    	$versioned = $instance->isVersioned();
    	
    	/*
		 * 
		 * 1. BUILD FORM
		 *
		 */
    	
		// File Content
		
    	$contentGroup = array();
    	function return_bytes($val) {
			$val = trim($val);
			$last = strtolower($val[strlen($val)-1]);
			switch($last) {
				// Le modifieur 'G' est disponible depuis PHP 5.1.0
				case 'g':
					$val *= 1024;
				case 'm':
					$val *= 1024;
				case 'k':
					$val *= 1024;
			}

			return $val;
		}
		$browseElt = tao_helpers_form_FormFactory::getElement("file_import", "AsyncFile");
		$browseElt->addValidator(tao_helpers_form_FormFactory::getValidator('FileSize', array('max' => return_bytes(ini_get('post_max_size')))));
    	//make the content compulsory if it does not exist already
		if(!$versioned){
			$browseElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
			$browseElt->setDescription(__("Upload the file to version"));
		}
		else{
			$browseElt->setDescription(__("Upload a new content"));
		}
		$this->form->addElement($browseElt);
		array_push($contentGroup, $browseElt->getName());
    	
		// if the file is yet versioned add a way to download it
		
		if($versioned){
			$downloadUrl = _url('downloadFile', 'File', 'tao', array(
					'uri' 		=> tao_helpers_Uri::encode($instance->uriResource),
					//'classUri' 	=> tao_helpers_Uri::encode($this->clazz->uriResource)
			));
			
			$downloadFileElt = tao_helpers_form_FormFactory::getElement("file_download", 'Free');
			$downloadFileElt->setValue("<a href='$downloadUrl' class='blink' target='_blank'><img src='".BASE_WWW."/img/text-xml-file.png' alt='xml' class='icon'  /> ".__('Download content')."</a>");
			$this->form->addElement($downloadFileElt);
			
			array_push($contentGroup, $downloadFileElt->getName());
		}
		
		$this->form->createGroup('file', 'Content', $contentGroup);
		
    	//File Meta
    	
    	$fileNameElt = tao_helpers_form_FormFactory::getElement(tao_helpers_Uri::encode(PROPERTY_FILE_FILENAME), $versioned ? 'Label' : 'Textbox');
		$fileNameElt->setDescription(__("File name"));
		if(!$versioned){ 
			$fileNameElt->addValidator(tao_helpers_form_FormFactory::getValidator('FileName'));
		}
		$this->form->addElement($fileNameElt);
		
		$filePathElt = tao_helpers_form_FormFactory::getElement(tao_helpers_Uri::encode(PROPERTY_VERSIONEDFILE_FILEPATH), $versioned ? 'Label' : 'Textbox');
		$filePathElt->setDescription(__("File path"));
		if(!$versioned){
			$filePathElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		}
		$this->form->addElement($filePathElt);
		
		$versionedRepositoryClass = new core_kernel_classes_Class(CLASS_GENERIS_VERSIONEDREPOSITORY);
		$repositoryEltOptions = array();
		foreach($versionedRepositoryClass->getInstances() as $repository){
			$repositoryEltOptions[tao_helpers_Uri::encode($repository->uriResource)] = $repository->getLabel();
		}
		$fileRepositoryElt = tao_helpers_form_FormFactory::getElement(tao_helpers_Uri::encode(PROPERTY_VERSIONEDFILE_REPOSITORY), $versioned ? 'Label' : 'Radiobox');
		$fileRepositoryElt->setDescription(__("File repository"));
		if(!$versioned){
			$fileRepositoryElt->setOptions($repositoryEltOptions);
		}
		$fileRepositoryElt->addValidator(tao_helpers_form_FormFactory::getValidator('NotEmpty'));
		$this->form->addElement($fileRepositoryElt);
    	
    	$this->form->createGroup('meta', 'Description', array(
			$fileNameElt->getName(),
			$filePathElt->getName(),
			$fileRepositoryElt->getName()
		));
    	
		// File Revision
		
		if($versioned){
			$fileVersionOptions = array();
			if($versioned){
				$history = $instance->gethistory();
				$countHistory = count($history);
				foreach($history as $i => $revision){
					$date = new DateTime($revision['date']);
					$fileVersionOptions[$countHistory-$i] = $countHistory-$i . '. ' . $revision['msg'] . ' [' . $revision['author'] .' / ' . $date->format('Y-m-d H:i:s') . '] ';
				}
			}
			
			$fileRevisionElt = tao_helpers_form_FormFactory::getElement('file_version', 'Radiobox');
			$fileRevisionElt->setDescription(__("File revision"));
			$fileRevisionElt->setOptions($fileVersionOptions);
			$this->form->addElement($fileRevisionElt);
			$this->form->createGroup('revision', 'Version', array($fileRevisionElt->getName()));
		}
		
		/*
		 * 
		 * 2. HIDDEN FIELDS
		 *
		 */
		
		//add an hidden elt for the property uri (Property associated to the owner instance)
		$propertyUriElt = tao_helpers_form_FormFactory::getElement("propertyUri", "Hidden");
		$propertyUriElt->setValue(tao_helpers_Uri::encode($property->uriResource));
		$this->form->addElement($propertyUriElt);
		
		//add an hidden elt for the instance Uri
		//usefull to render the revert action
		$instanceUriElt = tao_helpers_form_FormFactory::getElement('uri', 'Hidden');
		$instanceUriElt->setValue(tao_helpers_Uri::encode($ownerInstance->uriResource));
		$this->form->addElement($instanceUriElt);
		
		/*
		 * 
		 * 3. FILL THE FORM
		 *
		 */
		
    	if($versioned){
    		
			$fileNameValue = $instance->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_FILE_FILENAME));
			if(!empty($fileNameValue)){
				$fileNameElt = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_FILE_FILENAME));
				$fileNameElt->setValue($fileNameValue);
			}
		
			$filePathValue = $instance->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_VERSIONEDFILE_FILEPATH));
			if(!empty($filePathValue)){
				$filePathElt = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_VERSIONEDFILE_FILEPATH));
				$filePathElt->setValue($filePathValue);
			}
		
			$repositoryValue = $instance->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_VERSIONEDFILE_REPOSITORY));
			if(!empty($repositoryValue)){
				$repositoryElt = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_VERSIONEDFILE_REPOSITORY));
				$repositoryElt->setValue($repositoryValue->uriResource);
			}
			
			/*$versionValue = $instance->getOnePropertyValue(new core_kernel_classes_Property(PROPERTY_VERSIONEDFILE_VERSION));
			if(!empty($versionValue)){
				$versionElt = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_VERSIONEDFILE_VERSION));
				$versionElt->setValue($versionValue);
			}*/
		
			$history = $instance->gethistory();
			$versionElt = $this->form->getElement('file_version');
			$versionElt->setValue(count($history));
    	} 
    	
    	// DEFAULT VALUE
    	else {
    		$filePathElt->setValue('/');
    	}
    	
        // section 127-0-1-1-234d8e6a:13300ee5308:-8000:0000000000003F80 end
    }

    /**
     * Override the validate method of the form container to validate 
     * linked elements
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return boolean
     */
    public function validate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--485428cc:133267d2802:-8000:000000000000409D begin
        
    	if($this->form->isSubmited()){
    		$instance = new core_kernel_versioning_File($this->options['instanceUri']);
    		if($instance->isVersioned()){
    			return true;
    		}
    		
	    	$fileNameElt = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_FILE_FILENAME));
	    	$fileName = $fileNameElt->getValue();
	    	
	    	$filePathElt = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_VERSIONEDFILE_FILEPATH));
	    	$filePath = $filePathElt->getValue();
	    	
	    	$fileRepositoryElt = $this->form->getElement(tao_helpers_Uri::encode(PROPERTY_VERSIONEDFILE_REPOSITORY));
	    	$fileRepository = tao_helpers_Uri::decode($fileRepositoryElt->getValue());
	    	
	    	 //check if a resource with the same path exists yet in the repository
	        $clazz = new core_kernel_classes_Class(CLASS_GENERIS_VERSIONEDFILE);
	        $options = array('like' => false, 'recursive' => true);
			$propertyFilter = array(
				PROPERTY_FILE_FILENAME => $fileName,
				PROPERTY_VERSIONEDFILE_FILEPATH => $filePath,
				PROPERTY_VERSIONEDFILE_REPOSITORY => $fileRepository
			);
	        $sameNameFiles = $clazz->searchInstances($propertyFilter, $options);
	        if(!empty($sameNameFiles)){
	        	$sameFileResource = array_pop($sameNameFiles);
	        	$sameFile = new core_kernel_versioning_File($sameFileResource->uriResource);
	        	
	        	$this->form->valid = false;
	        	$this->form->error = __('A similar resource has already been versioned').' ('.$sameFile->getAbsolutePath().')';
	        }
    	}
    	
        // section 127-0-1-1--485428cc:133267d2802:-8000:000000000000409D end

        return (bool) $returnValue;
    }

} /* end of class tao_actions_form_VersionedFile */

?>