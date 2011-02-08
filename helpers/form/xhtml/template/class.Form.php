<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/xhtml/template/class.Form.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 08.02.2011, 17:24:17 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_xhtml_template
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_xhtml_Form
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/xhtml/class.Form.php');

/* user defined includes */
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EF7-includes begin
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EF7-includes end

/* user defined constants */
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EF7-constants begin
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EF7-constants end

/**
 * Short description of class tao_helpers_form_xhtml_template_Form
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_xhtml_template
 */
class tao_helpers_form_xhtml_template_Form
    extends tao_helpers_form_xhtml_Form
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function evaluate()
    {
        // section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EFD begin
        
    	$this->initElements();
		
		if(isset($_POST["{$this->name}_sent"])){
			
			$this->submited = true;
			
			//set posted values
			foreach($this->elements as $id => $element){
				
				if($element instanceof tao_helpers_form_elements_xhtml_File){
					
					if(isset($_FILES[$element->getName()])){
						$this->elements[$id]->setValue( 
							$_FILES[$element->getName()]
						);
					}
				}
				else if($element instanceof tao_helpers_form_elements_xhtml_Checkbox || $element instanceof tao_helpers_form_elements_xhtml_Treeview){
					$expression = "/^".preg_quote($element->getName(), "/")."(.)*[0-9]+$/";
					$found = false;
					foreach($_POST as $key => $value){
						if(preg_match($expression, $key)){
							$found = true;
							break;
						}
					}
					if($found){
						$this->elements[$id]->setValues(array());
						foreach($_POST as $key => $value){
							if(preg_match($expression, $key)){
								$this->elements[$id]->addValue(tao_helpers_Uri::decode($value));
							}
						}
					}
				}
				else if($element instanceof tao_helpers_form_elements_Template){
					$values = array();
					$prefix = preg_quote($element->getPrefix(), '/');
					foreach($_POST as $key => $value){
						if(preg_match("/^$prefix/", $key)){
							$values[$key] = $value;
						}
					}
					$element->setValues($values);
				}
				else{
					if(isset($_POST[$element->getName()])){
						if($element->getName() != 'uri' && $element->getName() != 'classUri'){
							$this->elements[$id]->setValue( 
								tao_helpers_Uri::decode($_POST[$element->getName()]) 
							);
						}
					}
				}
			}
			$this->validate();
		}
    	
        // section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004EFD end
    }

} /* end of class tao_helpers_form_xhtml_template_Form */

?>