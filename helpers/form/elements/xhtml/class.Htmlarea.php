<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 30.09.2009, 14:21:07 with ArgoUML PHP module 
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
 * include tao_helpers_form_elements_Htmlarea
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/elements/class.Htmlarea.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EA-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EA-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EA-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019EA-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Htmlarea
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Htmlarea
    extends tao_helpers_form_elements_Htmlarea
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

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019F4 begin
		 $returnValue .= "<label class='form_desc' for='{$this->name}'>".$this->getDescription()."</label>";
		 $returnValue .= "<textarea name='{$this->name}' id='{$this->name}' ";
		 $returnValue .= $this->renderAttributes();
		 $returnValue .= ">{$this->value}</textarea>";
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:00000000000019F4 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Htmlarea */

?>