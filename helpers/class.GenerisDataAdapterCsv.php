<?php

error_reporting(E_ALL);

/**
 * Adapter for CSV format
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @deprecated
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * This class enables you to manage interfaces with data. 
 * It provides the default prototype to adapt the data import/export from/to any
 * format.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/class.GenerisDataAdapter.php');

/* user defined includes */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAC-includes begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAC-includes end

/* user defined constants */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAC-constants begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAC-constants end

/**
 * Adapter for CSV format
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @deprecated
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CDC begin
		$this->options = $options;
		
		if(!isset($this->options['field_delimiter'])){
			$this->options['field_delimiter'] = ';';
		}				
		if(!isset($this->options['field_encloser'])){
			$this->options['field_encloser'] = '"';
		}
		if(!isset($this->options['line_break'])){
			$this->options['line_break'] = '\n';
		}
		if(!isset($this->options['multi_values_delimiter'])){
			$this->options['multi_values_delimiter'] = '|';
		}
		if(!isset($this->options['first_row_column_names'])){
			$this->options['first_row_column_names'] = true;
		}
		if(isset($this->options['column_order'])){
			$this->options['first_row_column_names'] = false;
		}
		else{
			$this->options['column_order'] = null;
		}
		
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CDC end
    }

    /**
     * Short description of method import
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string source
     * @param  Class destination
     * @return boolean
     */
    public function import($source,  core_kernel_classes_Class $destination)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAE begin
		
		//more readable in conacts
		$WRAP  = $this->options['field_encloser'];
		$DELIM = $this->options['field_delimiter'];
		$BREAK = "\n";
		$MULTI = $this->options['multi_values_delimiter'];
		
		$rows = explode($BREAK, $rows);
		
		if($this->options['first_row_column_names']){
			
			$fields = explode($DELIM, $rows[0]);
			unset($rows[0]);
			
		}
		else if(isset($this->options['column_order'])){
			$fields = $this->options['column_order'];
		}
		
		
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAE end

        return (bool) $returnValue;
    }

    /**
     * Short description of method export
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class source
     * @return string
     */
    public function export( core_kernel_classes_Class $source = null)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CB2 begin
		
		//more readable in conacts
		$WRAP  = $this->options['field_encloser'];
		$DELIM = $this->options['field_delimiter'];
		$BREAK = "\n";
		$MULTI = $this->options['multi_values_delimiter'];
		
		$properties = $this->getClassProperties($source);
		$index = 0;
		foreach($properties as $property){
			$returnValue .= $WRAP . addslashes($property->getLabel()) . $WRAP;
			($index < count($properties) - 1) ? $returnValue .= $DELIM :  $returnValue .= $BREAK ;
			$index++;
		}
		
		foreach($source->getInstances(false) as $instance){
		
			$index = 0;	
			foreach($properties	as $property){
				$exportedValue = '';
				$values = $instance->getPropertyValuesCollection($property);
				
				$pIndex = 0;
				foreach($values->getIterator() as $value){
					if($pIndex > 0 && $pIndex < $values->count()){
						$exportedValue .= $MULTI;
					}
					if(!is_null($value)){
						if($value instanceof core_kernel_classes_Resource){
							$exportedValue .= $value->getLabel();
						}
						if($value instanceof core_kernel_classes_Literal){
							$exportedValue .= (string)$value;
						}
					}
					$pIndex++;
				}
				
				$returnValue .= $WRAP . addslashes($exportedValue) . $WRAP;
				($index < count($properties) - 1) ? $returnValue .= $DELIM :  $returnValue .= $BREAK ;
				$index++;
			}
		}
		
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CB2 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_GenerisDataAdapterCsv */

?>