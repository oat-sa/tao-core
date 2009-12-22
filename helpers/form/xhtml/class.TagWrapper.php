<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/xhtml/class.TagWrapper.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 22.12.2009, 16:53:44 with ArgoUML PHP module 
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
 * include tao_helpers_form_Decorator
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/interface.Decorator.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000196F-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000196F-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000196F-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000196F-constants end

/**
 * Short description of class tao_helpers_form_xhtml_TagWrapper
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_xhtml
 */
class tao_helpers_form_xhtml_TagWrapper
        implements tao_helpers_form_Decorator
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute tag
     *
     * @access protected
     * @var string
     */
    protected $tag = 'div';

    /**
     * Short description of attribute id
     *
     * @access protected
     * @var string
     */
    protected $id = '';

    /**
     * Short description of attribute cssClass
     *
     * @access protected
     * @var string
     */
    protected $cssClass = '';

    // --- OPERATIONS ---

    /**
     * Short description of method preRender
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function preRender()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001952 begin
		if(!empty($this->tag)){
			$returnValue .= "<{$this->tag}";
			if(!empty($this->id)){
				$returnValue .= " id='{$this->id}' ";	
			}
			if(!empty($this->cssClass)){
				$returnValue .= " class='{$this->cssClass}' ";	
			}
			$returnValue .= ">";
		}
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001952 end

        return (string) $returnValue;
    }

    /**
     * Short description of method postRender
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function postRender()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001954 begin
		if(!empty($this->tag)){
			$returnValue .= "</{$this->tag}>";
		}
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001954 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getOption
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string key
     * @return string
     */
    public function getOption($key)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--704cb8ff:125262de5fb:-8000:0000000000001C79 begin
		if(isset($this->$key)){
			$returnValue = $this->$key;
		}
        // section 127-0-1-1--704cb8ff:125262de5fb:-8000:0000000000001C79 end

        return (string) $returnValue;
    }

    /**
     * Short description of method setOption
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string key
     * @param  string value
     * @return boolean
     */
    public function setOption($key, $value)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--704cb8ff:125262de5fb:-8000:0000000000001C7C begin
		
		$this->$key = $value;
		
        // section 127-0-1-1--704cb8ff:125262de5fb:-8000:0000000000001C7C end

        return (bool) $returnValue;
    }

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001976 begin
		if(isset($options['tag'])){
			$this->tag = $options['tag'];
		}
		if(isset($options['cssClass'])){
			$this->cssClass = $options['cssClass'];
		}
		if(isset($options['id'])){
			$this->id = $options['id'];
		}
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001976 end
    }

} /* end of class tao_helpers_form_xhtml_TagWrapper */

?>