<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/class.FormFactory.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 26.01.2010, 17:06:30 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form
 */
class tao_helpers_form_FormFactory
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute renderMode
     *
     * @access protected
     * @var string
     */
    protected static $renderMode = 'xhtml';

    // --- OPERATIONS ---

    /**
     * Short description of method setRenderMode
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string renderMode
     * @return mixed
     */
    public static function setRenderMode($renderMode)
    {
        // section 127-0-1-1--4d0d476d:124bee31dc8:-8000:0000000000001B2E begin
		self::$renderMode = $renderMode;
        // section 127-0-1-1--4d0d476d:124bee31dc8:-8000:0000000000001B2E end
    }

    /**
     * Short description of method getForm
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string name
     * @param  array options
     * @return tao_helpers_form_Form
     */
    public static function getForm($name = '', $options = array())
    {
        $returnValue = null;

        // section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B17 begin
		
		//use the right implementation (depending the render mode)
		//@todo refactor this and use a FormElementFactory
		switch(self::$renderMode){
			case 'xhtml':
				$myForm = new tao_helpers_form_xhtml_Form($name, $options);
				$myForm->setDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div')));
				$myForm->setGroupDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-group')));
				$myForm->setErrorDecorator(new tao_helpers_form_xhtml_TagWrapper(array('tag' => 'div', 'cssClass' => 'form-error ui-state-error ui-corner-all')));
				break;
			default: 
				throw new Exception("render mode {self::$renderMode} not yet supported");
		}
		
		$returnValue = $myForm;
		
        // section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B17 end

        return $returnValue;
    }

    /**
     * Short description of method getElement
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string name
     * @param  string type
     * @return tao_helpers_form_FormElement
     */
    public static function getElement($name = '', $type = '')
    {
        $returnValue = null;

        // section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B21 begin
		
		$eltClass = false;
		
		switch(self::$renderMode){
			case 'xhtml':
				$eltClass = "tao_helpers_form_elements_xhtml_{$type}";
				if(!class_exists($eltClass)){
					//throw new Exception("type $type not yet supported");
					return null;
				}
				break;
			default: 
				throw new Exception("render mode {self::$renderMode} not yet supported");
		}
		if($eltClass){
			$returnValue = new $eltClass($name);
			if(!$returnValue instanceof tao_helpers_form_FormElement){
				throw new Exception("$eltClass must be a tao_helpers_form_FormElement");
			}
		}
		
        // section 127-0-1-1--35d6051a:124bac7a23e:-8000:0000000000001B21 end

        return $returnValue;
    }

    /**
     * Short description of method getValidator
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string name
     * @param  array options
     * @return tao_helpers_form_Validator
     */
    public static function getValidator($name, $options = array())
    {
        $returnValue = null;

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BD2 begin
		
		$clazz = 'tao_helpers_form_validators_'.$name;
		if(class_exists($clazz)){
			$returnValue = new $clazz($options);
		}
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BD2 end

        return $returnValue;
    }

} /* end of class tao_helpers_form_FormFactory */

?>