<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/class.FormContainer.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 07.01.2010, 16:10:20 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form
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
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DCE-includes begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DCE-includes end

/* user defined constants */
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DCE-constants begin
// section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DCE-constants end

/**
 * Short description of class tao_helpers_form_FormContainer
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form
 */
abstract class tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute form
     *
     * @access protected
     * @var Form
     */
    protected $form = null;

    /**
     * Short description of attribute data
     *
     * @access protected
     * @var array
     */
    protected $data = array();

    /**
     * Short description of attribute options
     *
     * @access protected
     * @var array
     */
    protected $options = array();

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array data
     * @param  array options
     * @return mixed
     */
    public function __construct($data = array(), $options = array())
    {
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DDD begin
		
		$this->data = $data;
		$this->options = $options;
		
		$this->initForm();
		$this->initElements();
		
		if(count($this->data) > 0){
			$this->form->setValues($this->data);
		}
		
		if(!is_null($this->form)){
			$this->form->evaluate();
		}
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DDD end
    }

    /**
     * Short description of method getForm
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return tao_helpers_form_Form
     */
    public function getForm()
    {
        $returnValue = null;

        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DD9 begin
		
		$returnValue = $this->form;
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DD9 end

        return $returnValue;
    }

    /**
     * Short description of method initForm
     *
     * @abstract
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected abstract function initForm();

    /**
     * Short description of method initElements
     *
     * @abstract
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected abstract function initElements();

} /* end of abstract class tao_helpers_form_FormContainer */

?>