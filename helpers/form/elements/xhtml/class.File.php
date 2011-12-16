<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/xhtml/class.File.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 16.12.2011, 11:52:46 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_File
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.File.php');

/* user defined includes */
// section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CC9-includes begin
// section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CC9-includes end

/* user defined constants */
// section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CC9-constants begin
// section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CC9-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_File
 *
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_File
    extends tao_helpers_form_elements_File
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method render
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CCA begin
		
		if(!empty($this->value)){
			if(common_Utils::isUri($this->value)){
				$file = new core_kernel_classes_File($this->value);
				if($file->fileExists()){
					$fileInfo = $file->getFileInfo();
					$fileInfo->getFilename();
				}else{
					$file->delete();
				}
			}
		}
		
		$returnValue .= "<label class='form_desc' for='{$this->name}'>". _dh($this->getDescription())."</label>";
		$returnValue .= "<input type='hidden' name='MAX_FILE_SIZE' value='".tao_helpers_form_elements_File::MAX_FILE_SIZE."' />";
		$returnValue .= "<input type='file' name='{$this->name}' id='{$this->name}' ";
		$returnValue .= $this->renderAttributes();
		$returnValue .= " value='{$this->value}'  />";
		
        // section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CCA end

        return (string) $returnValue;
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     */
    public function evaluate()
    {
        // section 127-0-1-1-7109ddcd:1344660e25c:-8000:0000000000003482 begin
		if(isset($_FILES[$this->getName()])){
			$this->setValue($_FILES[$this->getName()]);
		}else{
			throw new tao_helpers_form_Exception('cannot evaluate the element '.__CLASS__);
		}
        // section 127-0-1-1-7109ddcd:1344660e25c:-8000:0000000000003482 end
    }

} /* end of class tao_helpers_form_elements_xhtml_File */

?>