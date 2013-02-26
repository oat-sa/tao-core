<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/class.AsyncFile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 25.02.2013, 17:24:31 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a FormElement entity
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 */
require_once('tao/helpers/form/class.FormElement.php');

/* user defined includes */
// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023D8-includes begin
// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023D8-includes end

/* user defined constants */
// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023D8-constants begin
// section 127-0-1-1--79a39ca7:12824fe53d5:-8000:00000000000023D8-constants end

/**
 * Short description of class tao_helpers_form_elements_AsyncFile
 *
 * @abstract
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_AsyncFile
    extends tao_helpers_form_FormElement
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---
	/**
	 * Short description of attribute widget
	 *
	 * @access public
	 * @var string
	 */
	public $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#AsyncFile';
	
    // --- OPERATIONS ---

    /**
     * Short description of method setValue
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  value
     * @return mixed
     */
    public function setValue($value)
    {
        // section 127-0-1-1-59174e95:13d1229b9b0:-8000:0000000000003C41 begin
    	if ($value instanceof tao_helpers_form_data_UploadFileDescription){
    		// The file is being uploaded.
    		$this->value = $value;
    	}
    	else if (common_Utils::isUri($value)){
    		// The file has already been uploaded
    		$file = new core_kernel_classes_File($value);
    		$this->value = new tao_helpers_form_data_StoredFileDescription($file);
    	}
    	else{
    		$this->value = ' ';
    	}

        // section 127-0-1-1-59174e95:13d1229b9b0:-8000:0000000000003C41 end
    }

} /* end of abstract class tao_helpers_form_elements_AsyncFile */

?>