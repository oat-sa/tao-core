<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013- (update and modification) Open Assessment Technologies SA 
 */

use oat\tao\helpers\data\ValidationException;
/**
 * Adapter for CSV format
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @deprecated
 * @package tao
 */
class tao_helpers_data_GenerisAdapterCsv extends tao_helpers_data_GenerisAdapter
{
    
    /**
     * Short description of attribute loadedFile
     *
     * @var tao_helpers_data_CsvFile
     */
    private $loadedFile = null;
    
    /**
     * Contains the callback functions to be applied on created resources.
     * 
     * @var array
     */
    protected $resourceImported = array();

    /**
     * Instantiates a new tao_helpers_data_GenerisAdapterCSV. The $options array
     * an associative array formated like this:
     * array('field_delimiter' => 'a delimiter char', default is ;,
     * 'field_encloser' => 'a field encloser char, default is "',
     * 'multi_values_delimiter' => 'a multi values delimiter, default is |',
     * 'first_row_column_names' => 'boolean value describing if the first row
     * column names').
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array $options
     * @return mixed
     */
    public function __construct($options = array())
    {
    	parent::__construct($options);
    	
    	if(!isset($this->options['field_delimiter'])){
			$this->options['field_delimiter'] = ';';
		}				
		if(!isset($this->options['field_encloser'])){
			$this->options['field_encloser'] = '"';		//double quote
		}
		if(!isset($this->options['multi_values_delimiter'])){
			$this->options['multi_values_delimiter'] = '|';
		}
		if(!isset($this->options['first_row_column_names'])){
			$this->options['first_row_column_names'] = true;
		}

		// Bind resource callbacks.
		if (isset($this->options['onResourceImported']) && is_array($this->options['onResourceImported'])){
		    foreach ($this->options['onResourceImported'] as $callback){
				$this->onResourceImported($callback);
				common_Logger::d("onResourceImported callback added to CSV Adapter");
			}
		}
    }

    /**
     * enable you to load the data in the csvFile to an associative array
     * the options
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string $csvFile
     * @return tao_helpers_data_CsvFile
     */
    public function load($csvFile)
    {
        $returnValue = null;

		$csv = new tao_helpers_data_CsvFile($this->options);
		$csv->load($csvFile);
		$this->loadedFile = $csv;
		$returnValue = $this->loadedFile;

        return $returnValue;
    }

    /**
     * Imports the currently loaded CsvFile into the destination Class.
     * The map should be set in the options before executing it.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string $source
     * @param  core_kernel_classes_Class $destination
     * @return common_report_Report
     */
    public function import($source,  core_kernel_classes_Class $destination = null)
    {
    	if(!isset($this->options['map'])){
        	throw new BadFunctionCallException("import map not set");
        }
        if(is_null($destination)){
        	throw new InvalidArgumentException("${destination} must be a valid core_kernel_classes_Class");
        }

        $csvData = $this->load($source);
        
        $createdResources = 0;
        $rangeProperty = new core_kernel_classes_Property(RDFS_RANGE);

    	for ($rowIterator = 0; $rowIterator < $csvData->count(); $rowIterator++){
    	    helpers_TimeOutHelper::setTimeOutLimit(helpers_TimeOutHelper::SHORT);
    		common_Logger::d("CSV - Importing CSV row ${rowIterator}.");
    		
			$resource = null;
			$csvRow = $csvData->getRow($rowIterator);

			//create the instance with the label defined in the map
			$label = $this->options['map'][RDFS_LABEL];
				
			try {
    			// validate
			    foreach ($this->options['map'] as $propUri => $csvColumn) {
			        $this->validate($destination, $propUri, $csvRow, $csvColumn);
			    }
			    
			    // evaluate values
			    $evaluatedData = array();
			    foreach($this->options['map'] as $propUri => $csvColumn){
			        
			        if ($csvColumn != 'csv_null' && $csvColumn != 'csv_select') {
			            // process value
			            if (isset($csvRow[$csvColumn]) && !is_null($csvRow[$csvColumn])) {
			                $property = new core_kernel_classes_Property($propUri);
			                $evaluatedData[$propUri] = $this->evaluateValues($csvColumn, $property, $csvRow[$csvColumn]);
			            }
			        } else {
			            // use defautl value
			            if (isset($this->options['staticMap'][$propUri]) && !is_null($this->options['staticMap'][$propUri])) {
			                $evaluatedData[$propUri] = $this->options['staticMap'][$propUri];
			                 
			            }
			        }
			    }
			    
			    // create resource
			    $resource = $destination->createInstanceWithProperties($evaluatedData);
			    
			    // Apply 'resourceImported' callbacks.
			    foreach ($this->resourceImported as $callback){
			        $callback($resource);
			    }
			    
			    $createdResources++;
			    
			} catch (tao_helpers_data_ValidationException $valExc) {
		        $targetProperty = new core_kernel_classes_Property($propUri);
			    $this->addErrorMessage(
			        $propUri,
			        common_report_Report::createFailure(
			            $label. ', ' .$valExc->getProperty()->getLabel(). ': ' .$valExc->getUserMessage(). ' "' . $valExc->getValue() . '"'
			        )
			    );
			    $valid = false;
			}
			
			helpers_TimeOutHelper::reset();
		}

		$this->addOption('to_import', count($csvData));
		$this->addOption('imported', $createdResources);

		$report = $this->getResult($createdResources);

		return $report;
    }

    /**
     * Short description of method export
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Class $source
     * @return boolean
     */
    public function export( core_kernel_classes_Class $source = null)
    {
        $returnValue = (bool) false;

        return (bool) $returnValue;
    }
    
    /**
     * 
     * @param unknown $column
     * @param core_kernel_classes_Property $property
     * @param unknown $value
     * @return array
     */
    protected function evaluateValues($column, core_kernel_classes_Property $property, $value)
    {
        $range = $property->getRange();
        // assume literal if no range defined
        $range = is_null($range) ? new core_kernel_classes_Class(RDFS_LITERAL) : $range;
        
        $evaluatedValue = $this->applyCallbacks($value, $this->options, $property);
        // ensure it's an array
        $evaluatedValue = is_array($evaluatedValue) ? $evaluatedValue : array($evaluatedValue);
        
        if ($range->getUri() != RDFS_LITERAL) {
            // validate resources
            foreach ($evaluatedValue as $key => $eVal) {
                $resource = new core_kernel_classes_Resource($eVal);
                if ($resource->exists()) {
                    if (!$resource->hasType($range)) {
                        // value outside of range
                        unset($evaluatedValue[$key]);
                    }
                } else {
                    // value not found
                    unset($evaluatedValue[$key]);
                }
            }
        }
        return $evaluatedValue;
    }

    /**
     * Short description of method applyCallbacks
     *
     * @access private
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string $value
     * @param  array $options
     * @param  core_kernel_classes_Property $targetProperty
     * @return string
     */
    private function applyCallbacks($value, $options,  core_kernel_classes_Property $targetProperty)
    {
        $returnValue = (string) '';
        
    	if(isset($options['callbacks'])){
			foreach(array('*', $targetProperty->getUri()) as $key){
				if(isset($options['callbacks'][$key]) && is_array($options['callbacks'][$key])){
					foreach ($options['callbacks'][$key] as $callback) {
						if(is_callable($callback)){
							$value = call_user_func($callback, $value);
						}
					}
				}
			}
		}
		
		$returnValue = $value;

        return (string) $returnValue;
    }

    public function onResourceImported(Closure $closure) {
		$this->resourceImported[] = $closure;
	}

	/**
	 * @param core_kernel_classes_Class $destination
	 * @param $propUri
	 * @param $csvRow
	 * @param $csvColumn
	 * @throws tao_helpers_data_ValidationException
	 * @return array
	 */
	protected function validate(core_kernel_classes_Class $destination, $propUri, $csvRow, $csvColumn)
	{
		/**  @var tao_helpers_form_Validator $validator */
		$validators = $this->getValidator($propUri);
		foreach ((array)$validators as $validator) {
            $validator->setOptions( array(
                'resourceClass' => $destination,
                'property'      => $propUri
            ));

            if (!$validator->evaluate($csvRow[$csvColumn])) {
                throw new ValidationException(new core_kernel_classes_Property($propUri), $csvRow[$csvColumn], $validator->getMessage());
			}
		}
		return true;
	}

	/**
	 * @param $createdResources
	 * @return common_report_Report
	 * @throws common_exception_Error
	 */
	protected function getResult($createdResources)
	{
        $message = __('Data imported');
		$type = common_report_Report::TYPE_SUCCESS;

		if ($this->hasErrors()) {
			$type = common_report_Report::TYPE_WARNING;
            $message = __('Data imported. Some records are invalid.');
		}

		if (!$createdResources) {
			$type = common_report_Report::TYPE_ERROR;
            $message = __('Data not imported. All records are invalid.');
		}

		$report = new common_report_Report($type, $message);
		foreach ($this->getErrorMessages() as $group) {
			$report->add($group);
		}

		return $report;
	}
}
