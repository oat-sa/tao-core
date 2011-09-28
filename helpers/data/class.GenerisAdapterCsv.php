<?php

error_reporting(E_ALL);

/**
 * Adapter for CSV format
 *
 * @author firstname and lastname of author, <author@example.org>
 * @deprecated
 * @package tao
 * @subpackage helpers_data
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * This class enables you to manage interfaces with data. 
 * It provides the default prototype to adapt the data import/export from/to any
 * format.
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('tao/helpers/data/class.GenerisAdapter.php');

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
 * @author firstname and lastname of author, <author@example.org>
 * @deprecated
 * @package tao
 * @subpackage helpers_data
 */
class tao_helpers_data_GenerisAdapterCsv
    extends tao_helpers_data_GenerisAdapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CDC begin
        
    	parent::__construct($options);
    	
    	if(!isset($this->options['field_delimiter'])){
			$this->options['field_delimiter'] = ';';
		}				
		if(!isset($this->options['field_encloser'])){
			$this->options['field_encloser'] = '"';		//double quote
		}
		if(!isset($this->options['line_break'])){
			$this->options['line_break'] = '\n';			// only to display use PHP_EOL in the code for a multi-os compat
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
     * enable you to load the data in the csvFile to an associative array
     * the options
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string csvFile
     * @return array
     */
    public function load($csvFile)
    {
        $returnValue = array();

        // section 127-0-1-1--250780b8:12843f3062f:-8000:0000000000002401 begin
        
        if(!file_exists($csvFile)){
        	throw new Exception("$csvFile not found");
        }   
        $fields = array();
        
        //more readable vars
    	$WRAP  = preg_quote($this->options['field_encloser'], '/');
		$DELIM = $this->options['field_delimiter'];
		$BREAK = PHP_EOL;
		$MULTI = $this->options['multi_values_delimiter'];
		
		
		$rows = explode($BREAK, file_get_contents($csvFile));
		
		if($this->options['first_row_column_names']){
			
			$fields = explode($DELIM, $rows[0]);
			foreach($fields as $i => $field){
				$fieldData = preg_replace("/^$WRAP/", '', $field);
				$fieldData = preg_replace("/$WRAP$/", '', $fieldData);
				$fields[$i] = $fieldData;
			}
			unset($rows[0]);
		}
		else if(isset($this->options['column_order'])){
			$fields = $this->options['column_order'];
		}
		if(count($fields) == 0){
			throw new Exception("No column is identified by the 1st row or by the column order field");
		}
		
		$lineNumber = 0;
		foreach($rows as  $row){
			if(trim($row) != ''){
				$returnValue[$lineNumber] = array();
				
				$rowFields = explode($DELIM, $row);
				$i = 0;
				foreach($fields as $field){
					$fieldData = preg_replace("/^$WRAP/", '', $rowFields[$i]);
					$fieldData = preg_replace("/$WRAP$/", '', $fieldData);
					$returnValue[$lineNumber][$field] = $fieldData;
					$i++;
				}	
				$lineNumber++;
			}
		}
        
		
        // section 127-0-1-1--250780b8:12843f3062f:-8000:0000000000002401 end

        return (array) $returnValue;
    }

    /**
     * Import a csv file (the source is the path of the file) into the
     * Class.
     * The map should be set in the options before executing it.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string source
     * @param  Class destination
     * @return boolean
     */
    public function import($source,  core_kernel_classes_Class $destination = null)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAE begin
        
        if(!isset($this->options['map'])){
        	throw new Exception("import map not set");
        }
        if(is_null($destination)){
        	throw new Exception("$destination must be a valid core_kernel_classes_Class");
        }
        
        $csvData = $this->load($source);
        
        $createdResources = 0;
		foreach($csvData as $csvRow){
			if(is_array($csvRow)){
				$resource = null;
				
				//create the instance with the label defined in the map 
				$label = $this->options['map'][RDFS_LABEL];
				
				if($label != 'empty' && $label != 'null'){
					if(isset($csvRow[$label])){
						$resource = $destination->createInstance($csvRow[$label]);
					}
				}
				if(is_null($resource)){
					$resource = $destination->createInstance();
				}				
				if($resource instanceof core_kernel_classes_Resource){
					
					//import the value of each column into the property defined in the map 
					foreach($this->options['map'] as $propUri => $csvColumn){
						if($propUri != RDFS_LABEL){		//already set 
							if($csvColumn != 'null'){
								if($csvColumn == 'empty'){
									$resource->setPropertyValue(new core_kernel_classes_Property($propUri), '');
								}
								if(isset($csvRow[$csvColumn])){
									$theValue = $csvRow[$csvColumn];
									if(isset($this->options['callbacks'])){
										
										foreach(array('*', $propUri) as $key){
											if(isset($this->options['callbacks'][$key]) && is_array($this->options['callbacks'][$key])){
												foreach ($this->options['callbacks'][$key] as $callback) {
													if(function_exists($callback)){
														$theValue = $callback($theValue);
													}
												}
											}
										}
										
									}
									
									$resource->setPropertyValue(new core_kernel_classes_Property($propUri), $theValue);
								}
							}
						}
					}
					foreach($this->options['staticMap'] as $propUri => $value){
						if(!array_key_exists($propUri, $this->options['map'])){
							if($propUri == RDF_TYPE){
								$resource->setType(new core_kernel_classes_Class($value));
							}
							else{
								$resource->setPropertyValue(new core_kernel_classes_Property($propUri), $value);
							}
						}
					}
					$createdResources++;
				}
			}
		}
        
		$this->addOption('to_import', count($csvData));
		$this->addOption('imported', $createdResources);
		
		if($createdResources > 0){
			$returnValue = true;
		}
        
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CAE end

        return (bool) $returnValue;
    }

    /**
     * Short description of method export
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  Class source
     * @return string
     */
    public function export( core_kernel_classes_Class $source = null)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CB2 begin
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001CB2 end

        return (string) $returnValue;
    }

    /**
     * Short description of method importLiteral
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @param  Property targetProperty
     * @param  Resource targetResource
     * @param  string csvRow
     * @param  string csvColumn
     * @return mixed
     */
    private function importLiteral( core_kernel_classes_Property $targetProperty,  core_kernel_classes_Resource $targetResource, $csvRow, $csvColumn)
    {
        // section 10-13-1-85--45c07818:132af6d7560:-8000:00000000000033C2 begin
        
    	if ($csvColumn == 'empty') {
    		// We do not use the value contained in $literal but an empty string.
    		$targetResource->setPropertyValue($targetProperty, '');
    	}
    	else if ($csvColumn != 'null' && $csvRow[$csvColumn] !== null) {
    		
    		$literal = $this->applyCallbacks($csvRow[$csvColumn], $this->options, $targetProperty);
    		$targetResource->setPropertyValue($targetProperty, $literal);
    	}
    	
        // section 10-13-1-85--45c07818:132af6d7560:-8000:00000000000033C2 end
    }

    /**
     * Short description of method importResource
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @param  Property targetProperty
     * @param  Resource targetResource
     * @param  string csvRow
     * @param  string csvColumn
     * @return mixed
     */
    private function importResource( core_kernel_classes_Property $targetProperty,  core_kernel_classes_Resource $targetResource, $csvRow, $csvColumn)
    {
        // section 10-13-1-85--673450cf:132af721862:-8000:00000000000033C8 begin
        
    	$useDefault = false;
    	
    	if ($csvColumn != 'empty' && $csvColumn != 'null') {
    		// We have to import the cell value as a resource for the target property.
    		$value = $csvRow[$csvColumn];
    		
    		if ($value != null) {
    			$value = $this->applyCallbacks($csvRow[$csvColumn], $this->options, $targetProperty);
    			$targetResource->setPropertyValue($targetProperty, $value);
    		}
    		else {
    			// No value for this cell. We should try to set a default value for this property.
    			$useDefault = true;
    		}
    	}
    	else {
    		// 
    		$useDefault = true;
    	}
    	
    	if ($useDefault) {
	    	$propUri = $targetProperty->uriResource;
	    	if (isset($staticMap[$propUri])) { // Do it only if a default value is provided.
	    		$value = $this->applyCallbacks($csvRow[$csvColumn], $this->options, $targetProperty);
	    		$targetResource->setPropertyValue($targetProperty, $value);
	    	}
    	}
    	
        // section 10-13-1-85--673450cf:132af721862:-8000:00000000000033C8 end
    }

    /**
     * Short description of method importStaticData
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @param  array staticMap
     * @param  array map
     * @param  Resource resource
     * @return mixed
     */
    private function importStaticData($staticMap, $map,  core_kernel_classes_Resource $resource)
    {
        // section 10-13-1-85--673450cf:132af721862:-8000:00000000000033CE begin
        
    	foreach($this->options['staticMap'] as $propUri => $value){
			$cleanUri = str_replace(TEMP_SUFFIX_CSV, '', $propUri);
			
			// If the property was not included in the original CSV file...
			if(!array_key_exists($cleanUri, $map)){
				if($propUri == RDF_TYPE){
					$resource->setType(new core_kernel_classes_Class($value));
				}
				else{
					$resource->setPropertyValue(new core_kernel_classes_Property($cleanUri), $value);
				}
			}
		}
    	
        // section 10-13-1-85--673450cf:132af721862:-8000:00000000000033CE end
    }

    /**
     * Short description of method applyCallbacks
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @param  string value
     * @param  array options
     * @param  Property targetProperty
     * @return string
     */
    private function applyCallbacks($value, $options,  core_kernel_classes_Property $targetProperty)
    {
        $returnValue = (string) '';

        // section 10-13-1-85--673450cf:132af721862:-8000:00000000000033D3 begin
        
    	if(isset($options['callbacks'])){
			foreach(array('*', $targetProperty->uriResource) as $key){
				if(isset($options['callbacks'][$key]) && is_array($options['callbacks'][$key])){
					foreach ($options['callbacks'][$key] as $callback) {
						if(function_exists($callback)){
							$value = $callback($value);
						}
					}
				}
			}
		}
		
		$returnValue = $value;
        
        // section 10-13-1-85--673450cf:132af721862:-8000:00000000000033D3 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_data_GenerisAdapterCsv */

?>