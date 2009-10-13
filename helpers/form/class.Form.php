<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/class.Form.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 13.10.2009, 14:34:11 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_FormElement
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/class.FormElement.php');

/**
 * include tao_helpers_form_Decorator
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/interface.Decorator.php');

/* user defined includes */
// section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018A4-includes begin
// section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018A4-includes end

/* user defined constants */
// section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018A4-constants begin
// section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018A4-constants end

/**
 * Short description of class tao_helpers_form_Form
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */
abstract class tao_helpers_form_Form
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd :     // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute elements
     *
     * @access protected
     * @var array
     */
    protected $elements = array();

    /**
     * Short description of attribute name
     *
     * @access protected
     * @var string
     */
    protected $name = '';

    /**
     * Short description of attribute decorator
     *
     * @access protected
     * @var Decorator
     */
    protected $decorator = null;

    /**
     * Short description of attribute valid
     *
     * @access protected
     * @var boolean
     */
    protected $valid = false;

    /**
     * Short description of attribute submited
     *
     * @access protected
     * @var boolean
     */
    protected $submited = false;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string name
     * @return mixed
     */
    public function __construct($name = '')
    {
        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:0000000000001912 begin
		$this->name = $name;
        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:0000000000001912 end
    }

    /**
     * Short description of method getName
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function getName()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:0000000000001918 begin
		$returnValue = $this->name;
        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:0000000000001918 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getElements
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return array
     */
    public function getElements()
    {
        $returnValue = array();

        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018AC begin
		$returnValue = $this->elements;
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018AC end

        return (array) $returnValue;
    }

    /**
     * Short description of method setElements
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  array elements
     * @return mixed
     */
    public function setElements($elements)
    {
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018B1 begin
		$this->elements = $elements;
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018B1 end
    }

    /**
     * Short description of method addElement
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  FormElement element
     * @return mixed
     */
    public function addElement( tao_helpers_form_FormElement $element)
    {
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018AE begin
		$this->elements[] = $element;
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018AE end
    }

    /**
     * Short description of method setDecorator
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Decorator decorator
     * @return mixed
     */
    public function setDecorator( tao_helpers_form_Decorator $decorator)
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001961 begin
		$this->decorator = $decorator;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001961 end
    }

    /**
     * Short description of method renderElements
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    protected function renderElements()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001983 begin
		foreach($this->elements as $element){
			 
			 if(!is_null($this->decorator) && $element->getWidget() != ''){
			 	$returnValue .= $this->decorator->preRender();
			 }
			 
			 $returnValue .= $element->render();
			 
			 if(!is_null($this->decorator) && $element->getWidget() != ''){
			 	$returnValue .= $this->decorator->postRender();
			 }
		}
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001983 end

        return (string) $returnValue;
    }

    /**
     * Short description of method initElements
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return mixed
     */
    protected function initElements()
    {
        // section 127-0-1-1-79c612e8:1244dcac11b:-8000:0000000000001A4E begin
		$tosort = array();
		foreach($this->elements as $i => $element){
			$tosort[$element->getLevel().'_'.$i] = $element;	//force string key
		}
		ksort($tosort);											//sort by key
		$this->elements = array();							
		foreach($tosort as $element){
			array_push($this->elements, $element); 
		}
		unset($tosort);
        // section 127-0-1-1-79c612e8:1244dcac11b:-8000:0000000000001A4E end
    }

    /**
     * Short description of method isValid
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return boolean
     */
    public function isValid()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019D3 begin
		$returnValue = $this->valid;
        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019D3 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method isSubmited
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return boolean
     */
    public function isSubmited()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019E0 begin
		$returnValue = $this->submited;
        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019E0 end

        return (bool) $returnValue;
    }

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

        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019E6 begin
		foreach($this->elements as $element){
			$returnValue[$element->getName()] = $element->getValue();
		}
        // section 127-0-1-1-7ebefbff:12428eef00b:-8000:00000000000019E6 end

        return (array) $returnValue;
    }

    /**
     * Short description of method evaluate
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return mixed
     */
    public abstract function evaluate();

    /**
     * Short description of method render
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public abstract function render();

} /* end of abstract class tao_helpers_form_Form */

?>