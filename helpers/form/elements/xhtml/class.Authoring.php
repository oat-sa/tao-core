<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 11.11.2009, 16:15:19 with ArgoUML PHP module 
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
 * include tao_helpers_form_elements_Authoring
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/elements/class.Authoring.php');

/* user defined includes */
// section 127-0-1-1-51e1cabd:124e3b3d559:-8000:0000000000001B4C-includes begin
// section 127-0-1-1-51e1cabd:124e3b3d559:-8000:0000000000001B4C-includes end

/* user defined constants */
// section 127-0-1-1-51e1cabd:124e3b3d559:-8000:0000000000001B4C-constants begin
// section 127-0-1-1-51e1cabd:124e3b3d559:-8000:0000000000001B4C-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Authoring
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Authoring
    extends tao_helpers_form_elements_Authoring
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

        // section 127-0-1-1-51e1cabd:124e3b3d559:-8000:0000000000001B4D begin
		$returnValue .= "<label class='form_desc' for='{$this->name}'>".$this->getDescription()."</label>";
		$returnValue .= "<input type='button' value='AUTHORING TOOL' />";
		
        // section 127-0-1-1-51e1cabd:124e3b3d559:-8000:0000000000001B4D end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Authoring */

?>