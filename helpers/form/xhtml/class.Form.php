<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/xhtml/class.Form.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 09.02.2010, 15:57:12 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a form. It provides the default behavior for form management and
 * be overridden for any rendering mode.
 * A form is composed by a set of FormElements.
 *
 * The form data flow is:
 * 1. add the elements to the form instance
 * 2. run evaluate (initElements, update states (submited, valid, etc), update
 * )
 * 3. render form
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.Form.php');

/* user defined includes */
// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EF-includes begin
// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EF-includes end

/* user defined constants */
// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EF-constants begin
// section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018EF-constants end

/**
 * Short description of class tao_helpers_form_xhtml_Form
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_xhtml
 */
class tao_helpers_form_xhtml_Form
    extends tao_helpers_form_Form
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getValues
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string groupName
     * @return array
     */
    public function getValues($groupName = '')
    {
        $returnValue = array();

        // section 127-0-1-1-4c3c2ff9:1242ef00aa7:-8000:0000000000001A1A begin
		foreach($this->elements as $element){
			if(!empty($groupName)){
				if(isset($this->groups[$groupName])){
					if(!in_array($element->getName(), $this->groups[$groupName]['elements'])){
						continue;
					}
				}
			}
			if($element instanceof tao_helpers_form_elements_xhtml_Checkbox){
				$returnValue[tao_helpers_Uri::decode($element->getName())] = array_map("tao_helpers_Uri::decode", $element->getValues());
			}
			else if($element instanceof tao_helpers_form_elements_xhtml_Treeview){
				$values = array_map("tao_helpers_Uri::decode", $element->getValues());
				if(count($values) == 1){
					$returnValue[tao_helpers_Uri::decode($element->getName())] = $values[0];
				}
				else{
					$returnValue[tao_helpers_Uri::decode($element->getName())] = $values;
				}
			}
			elseif($element instanceof tao_helpers_form_elements_xhtml_File || $element instanceof tao_helpers_form_elements_xhtml_AsyncFile){
				$returnValue[$element->getName()] = $element->getValue();
			}
			else{
				$returnValue[tao_helpers_Uri::decode($element->getName())] = tao_helpers_Uri::decode($element->getValue());
			}
		}
		unset($returnValue['uri']);
		unset($returnValue['classUri']);
        // section 127-0-1-1-4c3c2ff9:1242ef00aa7:-8000:0000000000001A1A end

        return (array) $returnValue;
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function evaluate()
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A33 begin
		
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
					$this->elements[$id]->setValues(array());
					foreach($_POST as $key => $value){
						if(preg_match($expression, $key)){
							$this->elements[$id]->addValue(tao_helpers_Uri::decode($value));
						}
					}
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
			
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A33 end
    }

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

        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018F0 begin
		
		(strpos($_SERVER['REQUEST_URI'], '?') > 0) ? $action = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $action = $_SERVER['REQUEST_URI'];
		
		$returnValue .= "<div class='xhtml_form'>\n";
		
		$returnValue .= "<form method='post' id='{$this->name}' name='{$this->name}' action='$action' ";
		if($this->hasFileUpload()){
			$returnValue .= "enctype='multipart/form-data' ";
		}
		$returnValue .= ">\n";
		
		$returnValue .= "<input type='hidden' name='{$this->name}_sent' value='1' />\n";
		
		
		$returnValue .= $this->renderActions('top');
		
		$returnValue .= $this->renderElements();
		
		$returnValue .= $this->renderActions('bottom');
		
		$returnValue .= "</form>\n";
        $returnValue .= "</div>\n";
		
        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018F0 end

        return (string) $returnValue;
    }

    /**
     * Short description of method validate
     *
     * @access private
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    private function validate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019E2 begin
		
		$this->valid = true;
		
		foreach($this->elements as $element){
			if(!$element->validate()){
				$this->valid = false;
			}
		}
		
        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019E2 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_xhtml_Form */

?>