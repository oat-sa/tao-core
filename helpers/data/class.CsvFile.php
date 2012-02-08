<?php

error_reporting(E_ALL);

/**
 * TAO - tao\helpers\data\class.CsvFile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 08.02.2012, 14:08:58 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_data
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 10-13-1-85--2776fceb:1355c97b33d:-8000:0000000000003AC8-includes begin
// section 10-13-1-85--2776fceb:1355c97b33d:-8000:0000000000003AC8-includes end

/* user defined constants */
// section 10-13-1-85--2776fceb:1355c97b33d:-8000:0000000000003AC8-constants begin
// section 10-13-1-85--2776fceb:1355c97b33d:-8000:0000000000003AC8-constants end

/**
 * Short description of class tao_helpers_data_CsvFile
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_data
 */
class tao_helpers_data_CsvFile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Contains the CSV data as a simple 2-dimensional array. Keys are integer
     * the mapping done separatyely if column names are provided.
     *
     * @access private
     * @var array
     */
    private $data = array();

    /**
     * Contains the mapping for column names if the CSV file contains a row
     * with column names.
     *
     * [0] ='id'
     * [1] = 'label'
     * ...
     *
     * If it has no name, empty string for this index.
     *
     * @access private
     * @var array
     */
    private $columnMapping = array();

    /**
     * Options such as string delimiter, new line escaping sequence, ...
     *
     * @access private
     * @var array
     */
    private $options = array();

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AD8 begin
        $defaults = array('field_delimiter' => ';',
        				  'field_encloser' => '"',
        				  'line_break' => "\n",
        				  'multi_values_delimiter' => '|',
        				  'first_row_column_names' => true,
        				  'column_order' => null);
        
        $this->setOptions(array_merge($defaults, $options));
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AD8 end
    }

    /**
     * Short description of method setData
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array data
     * @return void
     */
    protected function setData($data)
    {
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003ADA begin
        $this->data = $data;
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003ADA end
    }

    /**
     * Short description of method getData
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return array
     */
    public function getData()
    {
        $returnValue = array();

        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003ADD begin
        $returnValue = $this->data;
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003ADD end

        return (array) $returnValue;
    }

    /**
     * Short description of method setColumnMapping
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array columnMapping
     * @return void
     */
    protected function setColumnMapping($columnMapping)
    {
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003ADF begin
        $this->columnMapping = $columnMapping;
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003ADF end
    }

    /**
     * Short description of method getColumnMapping
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return array
     */
    protected function getColumnMapping()
    {
        $returnValue = array();

        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AE5 begin
        $returnValue = $this->columnMapping;
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AE5 end

        return (array) $returnValue;
    }

    /**
     * Short description of method load
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string path
     * @return void
     */
    public function load($path)
    {
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AE7 begin
        if (!is_file($path)) {
        	throw new InvalidArgumentException("Expected CSV file '${path}' could not be open.");
        }
        else if (!is_readable($path)) {
        	throw new InvalidArgumentException("CSV file '${path}' is not readable.");	
        }
        else {
        	// Let's try to read this !
        		
        }
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AE7 end
    }

    /**
     * Short description of method setOptions
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array array
     * @return void
     */
    public function setOptions($array = array())
    {
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AF2 begin
        $this->options = $array;
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AF2 end
    }

    /**
     * Short description of method getOptions
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return array
     */
    public function getOptions()
    {
        $returnValue = array();

        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AFF begin
        $returnValue = $this->options;
        // section 10-13-1-85-3961c2de:1355c9d169a:-8000:0000000000003AFF end

        return (array) $returnValue;
    }

    /**
     * Get a row at a given row $index.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  int index The row index. First = 0.
     * @param  boolean associative Says that if the keys of the array must be the column names or not. If $associative is set to true but there are no column names in the CSV file, an IllegalArgumentException is thrown.
     * @return array
     */
    public function getRow($index, $associative = false)
    {
        $returnValue = array();

        // section 10-13-1-85-4ddb0268:1355cfb6e4b:-8000:0000000000003B02 begin
        $data = $this->getData();
        if (isset($data[$index])) {
        	if ($associative == false) {
        		$returnValue = $data[$index];	
        	}
        	else {
        		if (count($this->columnMapping)) {
        			throw new InvalidArgumentException("Cannot access column mapping for this CSV file.");	
        		}
        	}
        }
        else {
        	throw new InvalidArgumentException("No row at index ${index}.");	
        }
        // section 10-13-1-85-4ddb0268:1355cfb6e4b:-8000:0000000000003B02 end

        return (array) $returnValue;
    }

    /**
     * Counts the number of rows in the CSV File.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return int
     */
    public function count()
    {
        $returnValue = (int) 0;

        // section 10-13-1-85-4ddb0268:1355cfb6e4b:-8000:0000000000003B28 begin
        $returnValue = count($this->getData());
        // section 10-13-1-85-4ddb0268:1355cfb6e4b:-8000:0000000000003B28 end

        return (int) $returnValue;
    }

    /**
     * Get the value at the specified $row,$col.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  int row Row index. If there is now row at $index, an IllegalArgumentException is thrown.
     * @param  int col
     * @return mixed
     */
    public function getValue($row, $col)
    {
        $returnValue = null;

        // section 10-13-1-85-4ddb0268:1355cfb6e4b:-8000:0000000000003B2D begin
        $data = $this->getData();
        if (isset($data[$row][$col])) {
        	$returnValue = $data[$row][$col];	
        }
        else if (isset($data[$row]) && is_string($col)) {
        	// try to access by col name.
        	$mapping = $this->getColumnMapping();
        	for ($i = 0; $i < count($mapping); $i++) {
        		
        		if ($mapping[$i] == $col && isset($data[$row][$col])) {
        			// Column with name $col extists.
        			$returnValue = $data[$row][$col];
        		}
        	}
        }
        else {
        	throw new InvalidArgumentException("No value at ${row},${col}.");	
        }
        // section 10-13-1-85-4ddb0268:1355cfb6e4b:-8000:0000000000003B2D end

        return $returnValue;
    }

    /**
     * Sets a value at the specified $row,$col.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  int row Row Index. If there is no such row, an IllegalArgumentException is thrown.
     * @param  int col
     * @param  int value The value to set at $row,$col.
     * @return void
     */
    public function setValue($row, $col, $value)
    {
        // section 10-13-1-85-4ddb0268:1355cfb6e4b:-8000:0000000000003B33 begin
        $data = $this->getData();
        if (isset($data[$row][$col])) {
        	$this->data[$row][$col] = $value;	
        } else if (isset($data[$row]) && is_string($col)) {
        	// try to access by col name.
        	$mapping = $this->getColumnMapping();
        	for ($i = 0; $i < count($mapping); $i++) {
        		
        		if ($mapping[$i] == $col && isset($data[$row][$col])) {
        			// Column with name $col extists.
        			$this->data[$row][$col] = $value;
        		}
        	}
        	
        	// Not found.
        	throw new InvalidArgumentException("Unknown column ${col}");
        }
        else {
        	throw new InvalidArgumentException("No value at ${row},${col}.");	
        }
        // section 10-13-1-85-4ddb0268:1355cfb6e4b:-8000:0000000000003B33 end
    }

} /* end of class tao_helpers_data_CsvFile */

?>