<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 01.02.2010, 15:59:09 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_Submit
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.Submit.php');

/* user defined includes */
// section 127-0-1-1-5e86b639:12689c55756:-8000:0000000000001E52-includes begin
// section 127-0-1-1-5e86b639:12689c55756:-8000:0000000000001E52-includes end

/* user defined constants */
// section 127-0-1-1-5e86b639:12689c55756:-8000:0000000000001E52-constants begin
// section 127-0-1-1-5e86b639:12689c55756:-8000:0000000000001E52-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Submit
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Submit
    extends tao_helpers_form_elements_Submit
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method render
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-5e86b639:12689c55756:-8000:0000000000001E53 begin
		
		if(is_null($this->value) || empty($this->value)){
			$this->value = __('Save');
		}
		$returnValue = "<input type='submit' id='{$this->name}' name='{$this->name}' ";
		$returnValue .= $this->renderAttributes();
		$returnValue .= ' value="'.htmlentities($this->value, ENT_COMPAT, 'UTF-8').'"  />';
		
        // section 127-0-1-1-5e86b639:12689c55756:-8000:0000000000001E53 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Submit */

?>
