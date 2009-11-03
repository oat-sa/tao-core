<?php

error_reporting(E_ALL);

/**
 * Represents a FormElement entity
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */
abstract class tao_helpers_form_FormElement
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute name
     *
     * @access protected
     * @var string
     */
    protected $name = '';

    /**
     * Short description of attribute value
     *
     * @access protected
     * @var mixed
     */
    protected $value = null;

    /**
     * Short description of attribute attributes
     *
     * @access protected
     * @var array
     */
    protected $attributes = array();

    /**
     * Short description of attribute widget
     *
     * @access protected
     * @var string
     */
    protected $widget = '';

    /**
     * Short description of attribute description
     *
     * @access protected
     * @var string
     */
    protected $description = '';

    /**
     * Short description of attribute level
     *
     * @access protected
     * @var int
     */
    protected $level = 1;

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
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018CA begin
		$this->name = $name;
        // section 10-13-1-45--48e788d1:123dcd97db5:-8000:00000000000018CA end
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

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A35 begin
		$returnValue = $this->name;
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A35 end

        return (string) $returnValue;
    }

    /**
     * Short description of method setName
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * Short description of method getValue
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return mixed
     */
    public function getValue()
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * Short description of method setAttributes
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function getDescription()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001A25 begin

		if(empty($this->description)){
			$returnValue = ucfirst($this->name);
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * Short description of method getLevel
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  int level
     * @return mixed
     */
    public function setLevel($level)
    {
        // section 127-0-1-1-79c612e8:1244dcac11b:-8000:0000000000001A4B begin
		$this->level = $level;
        // section 127-0-1-1-79c612e8:1244dcac11b:-8000:0000000000001A4B end
    }

} /* end of abstract class tao_helpers_form_FormElement */

?>