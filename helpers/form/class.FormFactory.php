<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/class.FormFactory.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 03.11.2009, 17:25:46 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B16-includes begin
// section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B16-includes end

/* user defined constants */
// section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B16-constants begin
// section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B16-constants end

/**
 * Short description of class tao_helpers_form_FormFactory
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */
class tao_helpers_form_FormFactory
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getForm
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string name
     * @param  string renderMode
     * @return tao_helpers_form_Form
     */
    public static function getForm($name = '', $renderMode = 'xhtml')
    {
        $returnValue = null;

        // section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B17 begin
		
		//use the right implementation (depending the render mode)
		//@todo refactor this and use a FormElementFactory
		switch($renderMode){
			case self::RENDER_MODE_XHTML:
				$myForm = new tao_helpers_form_xhtml_Form($name);
				$myForm->setDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')));
				$myForm->setGroupDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')));
				break;
			default: 
				throw new Exception("$renderMode not yet supported");
		}
		
		$returnValue = $myForm;
		
        // section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B17 end

        return $returnValue;
    }

    /**
     * Short description of method getElement
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string type
     * @param  string renderMode
     * @return tao_helpers_form_FormElement
     */
    public static function getElement($type, $renderMode = 'xhtml')
    {
        $returnValue = null;

        // section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B21 begin
        // section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B21 end

        return $returnValue;
    }

} /* end of class tao_helpers_form_FormFactory */

?>