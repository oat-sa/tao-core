<?php

error_reporting(E_ALL);

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
 * @package tao
 * @subpackage helpers_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

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
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */
abstract class tao_helpers_form_Form
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

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

    /**
     * Short description of attribute groups
     *
     * @access protected
     * @var array
     */
    protected $groups = array();

    /**
     * Short description of attribute groupDecorator
     *
     * @access protected
     * @var Decorator
     */
    protected $groupDecorator = null;

    /**
     * Short description of attribute options
     *
     * @access protected
     * @var array
     */
    protected $options = array();

    /**
     * Short description of attribute errorDecorator
     *
     * @access protected
     * @var Decorator
     */
    protected $errorDecorator = null;

    // --- OPERATIONS ---

    /**
     * the form constructor
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string name
     * @param  array options
     * @return mixed
     */
    public function __construct($name = '', $options = array())
    {
        // section 127-0-1-1--54ddf4d1:12404ee79c9:-8000:0000000000001912 begin
		$this->name = $name;
		$this->options = $options;
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
     * render all the form elements
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function renderElements()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001983 begin
		foreach($this->elements as $element){
			 
			 if($this->getElementGroup($element->getName()) != ''){
			 	continue;	//render grouped elements after  
			 }
			 
			 if(!is_null($this->decorator) && !($element instanceof tao_helpers_form_elements_Hidden)){
			 	$returnValue .= $this->decorator->preRender();
			 }
			 
			 //render element
			 $returnValue .= $element->render();
			 
			 //render error message
			 if(!$this->isValid() && $element->getError() != ''){
			 	if(!is_null($this->errorDecorator)){
			 		$returnValue .= $this->errorDecorator->preRender();
			 	}
			 	$returnValue .= $element->getError();
				if(!is_null($this->errorDecorator)){
			 		$returnValue .= $this->errorDecorator->postRender();
			 	}
			 }
			
			 
			 if(!is_null($this->decorator) && !($element instanceof tao_helpers_form_elements_Hidden)){
			 	$returnValue .= $this->decorator->postRender();
			 }
		}
		
		//render group
		foreach($this->groups as $groupName => $group){
		
			if(!is_null($this->groupDecorator)){
				$this->groupDecorator->setOption('id', tao_helpers_Display::textCleaner($groupName));
				$returnValue .= $this->groupDecorator->preRender();
			}
			$returnValue .= $group['title'];
			
			foreach($this->elements as $element){
				 if($this->getElementGroup($element->getName()) == $groupName){
				 
				 	if(!is_null($this->decorator) && !($element instanceof tao_helpers_form_elements_Hidden) ){
					 	$returnValue .= $this->decorator->preRender();
					 }
					 
					 //render element
					 $returnValue .= $element->render();
					 
					 //render error message
					 if(!$this->isValid() && $element->getError() != ''){
					 	if(!is_null($this->errorDecorator)){
					 		$returnValue .= $this->errorDecorator->preRender();
					 	}
					 	$returnValue .= $element->getError();
						if(!is_null($this->errorDecorator)){
					 		$returnValue .= $this->errorDecorator->postRender();
					 	}
					 }
					 
					 if(!is_null($this->decorator) && !($element instanceof tao_helpers_form_elements_Hidden) ){
					 	$returnValue .= $this->decorator->postRender();
					 }
				 
				 }
			}
			if(!is_null($this->groupDecorator)){
				$returnValue .= $this->groupDecorator->postRender();
				$this->groupDecorator->setOption('id', '');
			}
		}
        // section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001983 end

        return (string) $returnValue;
    }

    /**
     * initialize the elements set
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
			$tosort['0'.$element->getLevel()] = $element;	//force string key
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
     * Enables you to know if the form is valid
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
     * Enables you to know if the form has been submited
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
     * Short description of method getValue
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string name
     * @return boolean
     */
    public function getValue($name)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--6132c277:1244e864521:-8000:0000000000001A59 begin
		foreach($this->elements as $element){
			if($element->getName() == $name){
				return  $element->getValue();
			}
		}
        // section 127-0-1-1--6132c277:1244e864521:-8000:0000000000001A59 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method createGroup
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string groupName
     * @param  string groupTitle
     * @param  array elements
     * @return mixed
     */
    public function createGroup($groupName, $groupTitle = '', $elements = array())
    {
        // section 127-0-1-1--5420fa6f:12481873cb2:-8000:0000000000001ABB begin
		$this->groups[$groupName] = array(
			'title' 	=> (empty($groupTitle)) ? $groupName : $groupTitle,
			'elements'	=> $elements
		);
        // section 127-0-1-1--5420fa6f:12481873cb2:-8000:0000000000001ABB end
    }

    /**
     * Short description of method addToGroup
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string groupName
     * @param  string elementName
     * @return mixed
     */
    public function addToGroup($groupName, $elementName = '')
    {
        // section 127-0-1-1--5420fa6f:12481873cb2:-8000:0000000000001ACA begin
		
		if(isset($this->groups[$groupName])){
			if(isset($this->groups[$groupName]['elements'])){
				if(!in_array($elementName, $this->groups[$groupName]['elements'])){
					$this->groups[$groupName]['elements'][] = $elementName;
				}
			}
		}
		
        // section 127-0-1-1--5420fa6f:12481873cb2:-8000:0000000000001ACA end
    }

    /**
     * Short description of method getElementGroup
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string elementName
     * @return string
     */
    protected function getElementGroup($elementName)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--5420fa6f:12481873cb2:-8000:0000000000001ACF begin
		foreach($this->groups as $groupName => $group){
				if(in_array($elementName, $group['elements'])){
					$returnValue = $groupName;
					break;
				}
		}
        // section 127-0-1-1--5420fa6f:12481873cb2:-8000:0000000000001ACF end

        return (string) $returnValue;
    }

    /**
     * Short description of method setGroupDecorator
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Decorator decorator
     * @return mixed
     */
    public function setGroupDecorator( tao_helpers_form_Decorator $decorator)
    {
        // section 127-0-1-1--5420fa6f:12481873cb2:-8000:0000000000001AD9 begin
		
		$this->groupDecorator = $decorator;
		
        // section 127-0-1-1--5420fa6f:12481873cb2:-8000:0000000000001AD9 end
    }

    /**
     * Short description of method setErrorDecorator
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Decorator decorator
     * @return mixed
     */
    public function setErrorDecorator( tao_helpers_form_Decorator $decorator)
    {
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C04 begin
		
		$this->errorDecorator = $decorator;
		
        // section 127-0-1-1-34d7bcb9:1250bcb34b1:-8000:0000000000001C04 end
    }

    /**
     * evaluate the form inside the current context. Must be overridden, for
     * rendering mode: for example, it's used to populate and validate the data
     * the http request for an xhtml context
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return mixed
     */
    public abstract function evaluate();

    /**
     * Render the form. Must be overridden for each rendering mode.
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public abstract function render();

} /* end of abstract class tao_helpers_form_Form */

?>