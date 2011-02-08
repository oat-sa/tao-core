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
 * include tao_helpers_form_elements_Checkbox
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.Checkbox.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EC-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EC-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EC-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EC-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Checkbox
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Checkbox
    extends tao_helpers_form_elements_Checkbox
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

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019FC begin
		
		$i = 0;
		if(!isset($this->attributes['noLabel'])){
			$returnValue .= "<span class='form_desc'>"._dh($this->getDescription())."</span>";
		}
		else{
			unset($this->attributes['noLabel']);
		}
		$checkAll = false;
		if(isset($this->attributes['checkAll'])){
			$checkAll = (bool)$this->attributes['checkAll'];
			unset($this->attributes['checkAll']);
		}
		$checked = 0;
		foreach($this->options as $optionId => $optionLabel){
			 $returnValue .= "<input type='checkbox' value='{$optionId}' name='{$this->name}_{$i}' id='{$this->name}_{$i}' ";
			 $returnValue .= $this->renderAttributes();
			
			 if(in_array($optionId, $this->values)){
			 	$returnValue .= " checked='checked' ";	
			 	$checked++;
			 }
			 $returnValue .= " />&nbsp;<span class='elt_desc'>"._dh($optionLabel)."</span><br />";
			 $i++;
		}
		
		//add a small link 
		if($checkAll){
			if($checked == count($this->options)){
				$returnValue .= "<span class='checker-container'><a id='{$this->name}_checker' class='box-checker box-checker-uncheck' href='#'>".__('Uncheck All')."</a></span>";
			}
			else{
				$returnValue .= "<span class='checker-container'><a id='{$this->name}_checker' class='box-checker' href='#'>".__('Check All')."</a></span>";
			}
		}
		
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019FC end

        return (string) $returnValue;
    }

    /**
     * Short description of method setValue
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string value
     * @return mixed
     */
    public function setValue($value)
    {
        // section 127-0-1-1-bed3971:124720c750d:-8000:0000000000001A9A begin
		$this->addValue($value);
        // section 127-0-1-1-bed3971:124720c750d:-8000:0000000000001A9A end
    }

} /* end of class tao_helpers_form_elements_xhtml_Checkbox */

?>