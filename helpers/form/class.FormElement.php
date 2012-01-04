<?php

error_reporting(E_ALL);

/**
 * Represents a FormElement entity
 *
 * @author firstname and lastname of author, <author@example.org>
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
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('tao/helpers/form/class.Form.php');

/* user defined includes */
// section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018A5-includes begin
// section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018A5-includes end

/* user defined constants */
// section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018A5-constants begin
// section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018A5-constants end

/**
 * Represents a FormElement entity
 *
 * @abstract
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_form
 */
abstract class tao_helpers_form_FormElement
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * the name of the element
     *
     * @access protected
     * @var string
     */
    protected $name = '';

    /**
     * the value of the element
     *
     * @access protected
     * @var mixed
     */
    protected $value = null;

    /**
     * the list of element attributes (key/value pairs)
     *
     * @access protected
     * @var array
     */
    protected $attributes = array();

    /**
     * the widget links to the element
     *
     * @access protected
     * @var string
     */
    protected $widget = '';

    /**
     * the element description
     *
     * @access protected
     * @var string
     */
    protected $description = '';

    /**
     * used to display an element regarding the others
     *
     * @access protected
     * @var int
     */
    protected $level = 1;

    /**
     * The list of validators links to the elements
     *
     * @access protected
     * @var array
     */
    protected $validators = array();

    /**
     * the error message to display when the element validation has failed
     *
     * @access protected
     * @var string
     */
    protected $error = '';

    /**
     * to force the validation of the element
     *
     * @access protected
     * @var boolean
     */
    protected $forcedValid = false;

    /**
     * add a unit to the element (only for rendering purposes)
     *
     * @access protected
     * @var string
     */
    protected $unit = '';

    /**
     * Short description of attribute help
     *
     * @access protected
     * @var string
     */
    protected $help = '';

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string name
     * @return mixed
     */
    public function __construct($name = '')
    {
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018CA begin
		$this->name = $name;
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018CA end
    }

    /**
     * Short description of method getName
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getName()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A35 begin
		$returnValue = $this->name;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A35 end

        return (string) $returnValue;
    }

    /**
     * Short description of method setName
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string name
     * @return mixed
     */
    public function setName($name)
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001948 begin
        $this->name = $name;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001948 end
    }

    /**
     * Short description of method getRawValue
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function getRawValue()
    {
        $returnValue = null;

        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018D0 begin
        $returnValue = $this->value;
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018D0 end

        return $returnValue;
    }

    /**
     * Short description of method setValue
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string value
     * @return mixed
     */
    public function setValue($value)
    {
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018D2 begin
		$this->value = $value;
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018D2 end
    }

    /**
     * Short description of method addAttribute
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string key
     * @param  string value
     * @return mixed
     */
    public function addAttribute($key, $value)
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001919 begin
		$this->attributes[$key] = $value;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001919 end
    }

    /**
     * Short description of method setAttribute
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string key
     * @param  string value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // section 127-0-1-1-740c50e3:12704c0ea0d:-8000:0000000000001ECA begin
		$this->attributes[$key] = $value;
        // section 127-0-1-1-740c50e3:12704c0ea0d:-8000:0000000000001ECA end
    }

    /**
     * Short description of method setAttributes
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array attributes
     * @return mixed
     */
    public function setAttributes($attributes)
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000191D begin
		$this->attributes = $attributes;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000191D end
    }

    /**
     * Short description of method renderAttributes
     *
     * @access protected
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    protected function renderAttributes()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000194F begin
		foreach($this->attributes as $key => $value){
			$returnValue .= " {$key}='{$value}' "; 
		}
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000194F end

        return (string) $returnValue;
    }

    /**
     * Short description of method getWidget
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getWidget()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A0A begin
		$returnValue = $this->widget;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A0A end

        return (string) $returnValue;
    }

    /**
     * Short description of method getDescription
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getDescription()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A25 begin

		if(empty($this->description)){
			$returnValue = ucfirst(strtolower($this->name));
		}
		else{
			$returnValue = $this->description;
		}
		
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A25 end

        return (string) $returnValue;
    }

    /**
     * Short description of method setDescription
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string description
     * @return mixed
     */
    public function setDescription($description)
    {
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A27 begin
		$this->description = $description;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A27 end
    }

    /**
     * Short description of method setUnit
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string unit
     * @return mixed
     */
    public function setUnit($unit)
    {
        // section 127-0-1-1--5a8373f9:1272396a4bd:-8000:0000000000001EE3 begin
		$this->unit = $unit;
        // section 127-0-1-1--5a8373f9:1272396a4bd:-8000:0000000000001EE3 end
    }

    /**
     * Short description of method getLevel
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return int
     */
    public function getLevel()
    {
        $returnValue = (int) 0;

        // section 127-0-1-1-79c612e8:1244dcac11b:-8000:0000000000001A49 begin
		$returnValue = $this->level;
        // section 127-0-1-1-79c612e8:1244dcac11b:-8000:0000000000001A49 end

        return (int) $returnValue;
    }

    /**
     * Short description of method setLevel
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  int level
     * @return mixed
     */
    public function setLevel($level)
    {
        // section 127-0-1-1-79c612e8:1244dcac11b:-8000:0000000000001A4B begin
		$this->level = $level;
        // section 127-0-1-1-79c612e8:1244dcac11b:-8000:0000000000001A4B end
    }

    /**
     * Short description of method addValidator
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  Validator validator
     * @return mixed
     */
    public function addValidator( tao_helpers_form_Validator $validator)
    {
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BAE begin
		
		$this->validators[$validator->getName()] = $validator;
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BAE end
    }

    /**
     * Short description of method addValidators
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array validators
     * @return mixed
     */
    public function addValidators($validators)
    {
        // section 127-0-1-1-6a096e44:1254eebc226:-8000:0000000000001CE4 begin
		
		foreach($validators as $validator){
			$this->addValidator($validator);
		}
		
        // section 127-0-1-1-6a096e44:1254eebc226:-8000:0000000000001CE4 end
    }

    /**
     * Short description of method setForcedValid
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function setForcedValid()
    {
        // section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D7D begin
		$this->forcedValid = true;
        // section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D7D end
    }

    /**
     * Short description of method validate
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return boolean
     */
    public function validate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BC7 begin
		
		$returnValue = true;
		
		if(!$this->forcedValid){
			foreach($this->validators as $validator){
				if(!$validator->evaluate($this->getRawValue())){
					$this->error = $validator->getMessage();
					$returnValue = false;
					common_Logger::d(get_class($this).' is invalid for '.get_class($validator), array('TAO'));
					break;
				}
			}
		}
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BC7 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getError
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getError()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BD8 begin
		
		$returnValue = $this->error; 
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001BD8 end

        return (string) $returnValue;
    }

    /**
     * Short description of method setHelp
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string help
     * @return mixed
     */
    public function setHelp($help)
    {
        // section 127-0-1-1-435f81db:12d74b851ea:-8000:0000000000002BDB begin

    	$this->help = $help;
    	
        // section 127-0-1-1-435f81db:12d74b851ea:-8000:0000000000002BDB end
    }

    /**
     * Short description of method getHelp
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getHelp()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-435f81db:12d74b851ea:-8000:0000000000002BDE begin
        
        $returnValue = $this->help;
        
        // section 127-0-1-1-435f81db:12d74b851ea:-8000:0000000000002BDE end

        return (string) $returnValue;
    }

    /**
     * Short description of method removeValidator
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string name
     * @return boolean
     */
    public function removeValidator($name)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--1c42fdef:133c68cec06:-8000:0000000000003423 begin
		$name = (string) $name;
		if(strpos($name, 'tao_helpers_form_validators_') === 0){
			$name = str_replace('tao_helpers_form_validators_', '', $name);
		}
		if(isset($this->validators[$name])){
			unset($this->validators[$name]);
			$returnValue = true;
		}
		
        // section 127-0-1-1--1c42fdef:133c68cec06:-8000:0000000000003423 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method feed
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     */
    public function feed()
    {
        // section 127-0-1-1-7109ddcd:1344660e25c:-8000:000000000000347E begin
		if (isset($_POST[$this->name])) {
			if ($this->name != 'uri' && $this->name != 'classUri') {
				$this->setValue(tao_helpers_Uri::decode($_POST[$this->name]));
			}
		}
        // section 127-0-1-1-7109ddcd:1344660e25c:-8000:000000000000347E end
    }

    /**
     * Short description of method getEvaluatedValue
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function getEvaluatedValue()
    {
        $returnValue = null;

        // section 127-0-1-1--78214b38:13447752615:-8000:0000000000003486 begin
        $returnValue = tao_helpers_Uri::decode($this->getRawValue());
        // section 127-0-1-1--78214b38:13447752615:-8000:0000000000003486 end

        return $returnValue;
    }

    /**
     * Legacy code compliance method. When the getRawValue and the
     * methods were added, the fact that the getValue method was still invoked
     * TAO was not taken into account. To solve the problem, the getValue method
     * added to this class. Its implementation will call the getRawValue method
     * it has the same behaviour as the old getValue.
     *
     * @access public
     * @author Jérôme Bogaerts
     * @deprecated
     * @return mixed
     */
    public function getValue()
    {
        $returnValue = null;

        // section 10-13-1-85--61757c92:134a803677f:-8000:0000000000003835 begin
        return $this->getRawValue();
        // section 10-13-1-85--61757c92:134a803677f:-8000:0000000000003835 end

        return $returnValue;
    }

} /* end of abstract class tao_helpers_form_FormElement */

?>