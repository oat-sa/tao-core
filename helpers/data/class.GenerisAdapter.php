<?php

error_reporting(E_ALL);

/**
 * This class enables you to manage interfaces with data. 
 * It provides the default prototype to adapt the data import/export from/to any
 * format.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_data
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-includes begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-includes end

/* user defined constants */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-constants begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-constants end

/**
 * This class enables you to manage interfaces with data. 
 * It provides the default prototype to adapt the data import/export from/to any
 * format.
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_data
 */
abstract class tao_helpers_data_GenerisAdapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

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
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C97 begin
        
    	$this->options = $options;
    	
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C97 end
    }

    /**
     * get the adapter options
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public function getOptions()
    {
        $returnValue = array();

        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CC0 begin
        
        $returnValue = $this->options;
        
        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CC0 end

        return (array) $returnValue;
    }

    /**
     * set the adapter options
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function setOptions($options = array())
    {
        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CC3 begin
        
    	$this->options = $options;
    	
        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CC3 end
    }

    /**
     * add a new option
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string name
     * @param  value
     * @return mixed
     */
    public function addOption($name, $value)
    {
        // section 127-0-1-1--250780b8:12843f3062f:-8000:0000000000002412 begin
        
    	$this->options[$name] = $value;
    	
        // section 127-0-1-1--250780b8:12843f3062f:-8000:0000000000002412 end
    }

    /**
     * import prototype: import the source into the destination class
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string source
     * @param  Class destination
     * @return boolean
     */
    public abstract function import($source,  core_kernel_classes_Class $destination = null);

    /**
     * export prototype: export the source class
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class source
     * @return string
     */
    public abstract function export( core_kernel_classes_Class $source = null);

} /* end of abstract class tao_helpers_data_GenerisAdapter */

?>