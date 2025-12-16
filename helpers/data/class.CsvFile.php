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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *               2012-2018 (update and modification) Open Assessment Technologies SA;
 *
 */

use oat\oatbox\filesystem\File;

/**
 * Short description of class tao_helpers_data_CsvFile
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 */
class tao_helpers_data_CsvFile
{
    public const FIELD_DELIMITER = 'field_delimiter';
    public const FIELD_ENCLOSER = 'field_encloser';
    public const MULTI_VALUES_DELIMITER = 'multi_values_delimiter';
    public const FIRST_ROW_COLUMN_NAMES = 'first_row_column_names';
    /**
         * Contains the CSV data as a simple 2-dimensional array. Keys are integer
         * the mapping done separatyely if column names are provided.
         *
         * @access private
         * @var array
         */
    private $data = [];
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
    private $columnMapping = [];
    /**
         * Options such as string delimiter, new line escaping sequence, ...
         *
         * @access private
         * @var array
         */
    private $options = [];
    /**
         * The count of columns in the CsvFile. Will be updated at each row
         * The largest count will be taken into account.
         *
         * @access private
         * @var Integer
         */
    private $columnCount = null;
    /**
         * Short description of method __construct
         *
         * @access public
         * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
         * @param  array options
         * @return mixed
         */
    public function __construct($options = [])
    {
        $defaults = ['field_delimiter' => ';',
            'field_encloser' => '"',
            // if empty - don't use multi_values
            'multi_values_delimiter' => '',
            'first_row_column_names' => true];
        $this->setOptions(array_merge($defaults, $options));
        $this->setColumnCount(0);
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
        $this->data = $data;
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
        return (array)$this->data;
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
        $this->columnMapping = $columnMapping;
    }

    /**
     * Short description of method getColumnMapping
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return array
     */
    public function getColumnMapping()
    {
        return (array)$this->columnMapping;
    }

    /**
     * Load the file and parse csv lines
     *
     * Extract headers if `first_row_column_names` is in $this->options
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string $source
     * @return void
     */
    public function load($source)
    {
        if ($source instanceof File) {
            $resource = $source->readStream();
        } else {
            if (!is_file($source)) {
                throw new InvalidArgumentException("Expected CSV file '" . $source . "' could not be open.");
            }
            if (!is_readable($source)) {
                throw new InvalidArgumentException("CSV file '" . $source . "' is not readable.");
            }
            $resource = fopen($source, 'r');
        }

        // More readable variables
        $enclosure = preg_quote($this->options['field_encloser'], '/');
        $delimiter = $this->options['field_delimiter'];
        $multiValueSeparator = $this->options['multi_values_delimiter'];
        $adle = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', true);
        if ($this->options['first_row_column_names']) {
            $fields = fgetcsv($resource, 0, $delimiter, $enclosure);
            $this->setColumnMapping($fields);
        }

        $data = [];
        while (($rowFields = fgetcsv($resource, 0, $delimiter, $enclosure)) !== false) {
            $lineData = [];
            foreach ($rowFields as $fieldData) {
                // If there is nothing in the cell, replace by null for abstraction consistency.
                if ($fieldData == '') {
                    $fieldData = null;
                } elseif (!empty($multiValueSeparator) && mb_strpos($fieldData, $multiValueSeparator) !== false) {
                    // try to split by multi_value_delimiter
                    $multiField = [];
                    foreach (explode($multiValueSeparator, $fieldData) as $item) {
                        if (!empty($item)) {
                            $multiField[] = $item;
                        }
                    }
                    $fieldData = $multiField;
                }
                $lineData[] = $fieldData;
            }
            $data[] = $lineData;
            // Update the column count.
            $currentRowColumnCount = count($rowFields);
            if ($this->getColumnCount() < $currentRowColumnCount) {
                $this->setColumnCount($currentRowColumnCount);
            }
        }
        ini_set('auto_detect_line_endings', $adle);
        fclose($resource);
        $this->setData($data);
    }

    /**
     * Short description of method setOptions
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array array
     * @return void
     */
    public function setOptions($array = [])
    {
        $this->options = $array;
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
        return (array)$this->options;
    }

    /**
     * Get a row at a given row $index.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param int $index The row index. First = 0.
     * @param boolean $associative Says that if the keys of the array must be the column names or not. If $associative
     *                             is set to true but there are no column names in the CSV file, an
     *                             IllegalArgumentException is thrown.
     * @return array
     */
    public function getRow($index, $associative = false)
    {
        $data = $this->getData();
        if (isset($data[$index])) {
            if ($associative == false) {
                $returnValue = $data[$index];
            } else {
                $mapping = $this->getColumnMapping();
                if (!count($mapping)) {
                    // Trying to access by column name but no mapping detected.
                    throw new InvalidArgumentException("Cannot access column mapping for this CSV file.");
                } else {
                    $mappedRow = [];
                    for (
                        $i = 0; $i < count($mapping); $i++
                    ) {
                        $mappedRow[$mapping[$i]] = $data[$index][$i];
                    }
                    $returnValue = $mappedRow;
                }
            }
        } else {
            throw new InvalidArgumentException("No row at index {$index}.");
        }

        return (array)$returnValue;
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
        return (int)count($this->getData());
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
        $data = $this->getData();
        if (isset($data[$row][$col])) {
            $returnValue = $data[$row][$col];
        } elseif (isset($data[$row]) && is_string($col)) {
            // try to access by col name.
            $mapping = $this->getColumnMapping();
            for (
                $i = 0; $i < count($mapping); $i++
            ) {
                if ($mapping[$i] == $col && isset($data[$row][$col])) {
                    // Column with name $col extists.
                    $returnValue = $data[$row][$col];
                }
            }
        } else {
            throw new InvalidArgumentException("No value at {$row},{$col}.");
        }
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
        $data = $this->getData();
        if (isset($data[$row][$col])) {
            $this->data[$row][$col] = $value;
        } elseif (isset($data[$row]) && is_string($col)) {
            // try to access by col name.
            $mapping = $this->getColumnMapping();
            for (
                $i = 0; $i < count($mapping); $i++
            ) {
                if ($mapping[$i] == $col && isset($data[$row][$col])) {
                    // Column with name $col extists.
                    $this->data[$row][$col] = $value;
                }
            }
            // Not found.
            throw new InvalidArgumentException("Unknown column {$col}");
        } else {
            throw new InvalidArgumentException("No value at {$row},{$col}.");
        }
    }

    /**
     * Gets the count of columns contained in the CsvFile.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return int
     */
    public function getColumnCount()
    {
        return (int)$this->columnCount;
    }

    /**
     * Sets the column count.
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  int count The column count.
     * @return void
     */
    protected function setColumnCount($count)
    {
        $this->columnCount = $count;
    }
}
