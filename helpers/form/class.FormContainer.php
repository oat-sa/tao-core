<?php

error_reporting(E_ALL);

/**
 * This class provide a container for a specific form instance.
 * It's subclasses instanciate a form and it's elements to be used as a
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
 * This class provide a container for a specific form instance.
 * It's subclasses instanciate a form and it's elements to be used as a
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

    /**
     * list of all instanciated forms
     *
     * @access protected
     * @var array
     */
    protected static $forms = array();

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
		
		//initialize the form attribute
		$this->initForm();
		
		if(!is_null($this->form)){
			//let the refs of all the forms there 
			self::$forms[$this->form->getName()] = $this->form;
		}
		
		//initialize the elmements of the form
		$this->initElements();
		
		//set the values in case of default values
		if(count($this->data) > 0){
			$this->form->setValues($this->data);
		}
		
		//evaluate the form
		if(!is_null($this->form)){
			$this->form->evaluate();
		}
		
        // section 127-0-1-1-1f533553:1260917dc26:-8000:0000000000001DDD end
    }

    /**
     * get the form instance
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
     * Must be overriden and must instanciate the form instance and put it in
     * form attribute
     *
     * @abstract
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected abstract function initForm();

    /**
     * Used to create the form elements and bind them to the form instance
     *
     * @abstract
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected abstract function initElements();

} /* end of abstract class tao_helpers_form_FormContainer */

?>