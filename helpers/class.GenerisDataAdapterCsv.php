<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/class.GenerisDataAdapterCsv.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 30.11.2009, 18:13:29 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_GenerisDataAdapter
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/class.GenerisDataAdapter.php');

/* user defined includes */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAC-includes begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAC-includes end

/* user defined constants */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAC-constants begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAC-constants end

/**
 * Short description of class tao_helpers_GenerisDataAdapterCsv
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_GenerisDataAdapterCsv
    extends tao_helpers_GenerisDataAdapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CDC begin
		
		if(count($options) == 0){
			$this->options = array(
				'delimiter'					=> ';',
				'text_wrapper'				=> '"',
				'line_break'				=> '\n',
				'multi_values_delimiter'	=> '|'
			);
		}
		else{
			$this->options = $options;
		}
		
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CDC end
    }

    /**
     * Short description of method import
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string source
     * @param  Class destination
     * @return boolean
     */
    public function import($source,  core_kernel_classes_Class $destination)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAE begin
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAE end

        return (bool) $returnValue;
    }

    /**
     * Short description of method export
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class source
     * @return string
     */
    public function export( core_kernel_classes_Class $source)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CB2 begin
		
		$WRAP  = $this->options['text_wrapper'];
		$DELIM = $this->options['delimiter'];
		$BREAK = $this->options['line_break'];
		$MULTI = $this->options['multi_values_delimiter'];
		
		$properties = $source->getProperties(false); 
		$index = 0;
		foreach($properties as $property){
			$returnValue .= $WRAP . addslashes($property->getLabel()) . $WRAP;
			($index < count($properties)) ? $returnValue .= $DELIM :  $returnValue .= $BREAK ;
			$index++;
		}
		
		foreach($source->getInstances(false) as $instance){
		
			$index = 0;	
			foreach($properties	as $property){
				$value = '';
				$values = $instance->getPropertyValues($property);
				if(count($values) > 1){
					$value = implode($MULTI, $values);
				}
				elseif(count($values) == 1){
					$value = $values[0];
				}
				$returnValue .= $WRAP . addslashes($values[0]) . $WRAP;
				($index < count($properties)) ? $returnValue .= $DELIM :  $returnValue .= $BREAK ;
				$index++;
			}
		}
		
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CB2 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_GenerisDataAdapterCsv */

?>