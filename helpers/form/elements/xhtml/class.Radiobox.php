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
 * include tao_helpers_form_elements_Radiobox
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.Radiobox.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EB-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EB-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EB-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EB-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Radiobox
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Radiobox
    extends tao_helpers_form_elements_Radiobox
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

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019F6 begin
		
		$i = 0;
		
		if(!isset($this->attributes['noLabel'])){
			$returnValue .= "<span class='form_desc'>". _dh($this->getDescription())."</span>";
		}
		else{
			unset($this->attributes['noLabel']);
		}
		$returnValue .= '<div class="form_radlst">';
		foreach($this->options as $optionId => $optionLabel){
			 $returnValue .= "<input type='radio' name='{$this->name}' id='{$this->name}_{$i}' value='{$optionId}' ";
			 $returnValue .= $this->renderAttributes();
			 if($this->value == $optionId){
			 	$returnValue .= " checked='checked' ";
			 }
			 $returnValue .= " /><label class='elt_desc' for='{$this->name}_{$i}'>"._dh($optionLabel)."</label><br />";
			 $i++;
		}
		$returnValue .= "</div>";
		
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019F6 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Radiobox */

?>