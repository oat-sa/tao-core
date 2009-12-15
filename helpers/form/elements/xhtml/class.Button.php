<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 02.11.2009, 12:38:10 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_Button
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/elements/class.Button.php');

/* user defined includes */
// section 127-0-1-1--5dde9503:124b4a68f24:-8000:0000000000001B12-includes begin
// section 127-0-1-1--5dde9503:124b4a68f24:-8000:0000000000001B12-includes end

/* user defined constants */
// section 127-0-1-1--5dde9503:124b4a68f24:-8000:0000000000001B12-constants begin
// section 127-0-1-1--5dde9503:124b4a68f24:-8000:0000000000001B12-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Button
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Button
    extends tao_helpers_form_elements_Button
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

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

        // section 127-0-1-1--5dde9503:124b4a68f24:-8000:0000000000001B13 begin
		
		if(!empty($this->description)){
			$returnValue .= "<label class='form_desc' for='{$this->name}'>".$this->getDescription()."</label>";
		}
		$returnValue .= "<input type='button' name='{$this->name}' id='{$this->name}' ";
		$returnValue .= $this->renderAttributes();
		$returnValue .= " value='{$this->value}'  />";
		
        // section 127-0-1-1--5dde9503:124b4a68f24:-8000:0000000000001B13 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Button */

?>