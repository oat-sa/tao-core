<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/xhtml/class.Form.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 07.10.2009, 14:49:20 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_Form
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return array
     */
    public function getValues()
    {
        $returnValue = array();

        // section 127-0-1-1-4c3c2ff9:1242ef00aa7:-8000:0000000000001A1A begin
		foreach($this->elements as $element){
			if($element instanceof tao_helpers_form_elements_xhtml_Checkbox){
				
				$returnValue[tao_helpers_Uri::decode($element->getName())] = array();
				foreach($element->getValues() as $curValue){
					array_push($returnValue[tao_helpers_Uri::decode($element->getName())], tao_helpers_Uri::decode($curValue));
				}
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
				if($element instanceof tao_helpers_form_elements_xhtml_Checkbox){
					foreach($element->getOptions() as $optionId => $option){
						if(isset($_POST[$optionId])){
							$this->elements[$id]->addValue(tao_helpers_Uri::decode($optionId));
						}
					}
				}
				else{
					if(isset($_POST[$element->getName()])){
						$this->elements[$id]->setValue( 
							tao_helpers_Uri::decode($_POST[$element->getName()]) 
						);
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018F0 begin
		
		(strpos($_SERVER['REQUEST_URI'], '?') > 0) ? $action = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) : $action = $_SERVER['REQUEST_URI'];
		
		$returnValue .= "<div class='xhtml_form'>";
		$returnValue .= "<form method='post' id='{$this->name}' name='{$this->name}' action='$action'>";
		$returnValue .= $this->renderElements();
		
		 if(!is_null($this->decorator)){
		 	$returnValue .= $this->decorator->preRender();
		 }
		 $returnValue .= "<input type='hidden' name='{$this->name}_sent' value='1' />";
		 $returnValue .= "<input type='submit' value='".__('save')."'  />";
		 $returnValue .= "<input type='button' value='".__('revert'). "' class='form-reverter' />";
		 if(!is_null($this->decorator)){
		 	$returnValue .= $this->decorator->postRender();
		 }
        
		$returnValue .= "</form>";
        $returnValue .= "</div>";
		
        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:00000000000018F0 end

        return (string) $returnValue;
    }

    /**
     * Short description of method validate
     *
     * @access private
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return boolean
     */
    private function validate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019E2 begin
		
		//@todo validate the form
		
		$this->valid = true;
        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019E2 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_xhtml_Form */

?>