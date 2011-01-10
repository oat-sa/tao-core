<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 22.12.2009, 16:53:45 with ArgoUML PHP module 
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
 * include tao_helpers_form_elements_Textarea
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.Textarea.php');

/* user defined includes */
// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EA-includes begin
// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EA-includes end

/* user defined constants */
// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EA-constants begin
// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EA-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Textarea
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Textarea
    extends tao_helpers_form_elements_Textarea
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

        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EB begin
		
		if(!isset($this->attributes['noLabel'])){
			$returnValue .= "<label class='form_desc' for='{$this->name}'>". _dh($this->getDescription())."</label>";
		}
		else{
			unset($this->attributes['noLabel']);
		}
		$returnValue .= "<textarea name='{$this->name}' id='{$this->name}' ";
		$returnValue .= $this->renderAttributes();
		$returnValue .= ">"._dh($this->value)."</textarea>";
        
		// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EB end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Textarea */

?>
