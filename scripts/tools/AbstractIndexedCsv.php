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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\tools;

use oat\oatbox\action\Action;
use \common_report_Report as Report;

/**
 * Abstract Indexed CSV Script.
 * 
 * This abstract script aims at providing the basis to scripts
 * aiming at analyzing CSV files as indexed files.
 * 
 * The index works by being provided a column index. It will record
 * the position (in bytes) of all records based on this column index.
 */
abstract class AbstractIndexedCsv implements Action
{
    private $headers;
    private $firstRowColumnNames;
    private $indexColumn;
    private $source;
    private $destination;
    private $sourceFp;
    private $destinationFp;
    private $index;
    private $params;
    
    /**
     * Script Invokation.
     * 
     * This method contains the main logic of the abstraction.
     * 
     * @array $params The script parameters
     */
    public function __invoke($params)
    {
        $this->setParams($params);
        $this->setHeaders([]);
        $this->setFirstRowColumnNames(false);
        $this->setIndexColumn(0);
        
        // -- Deal with parameters.
        if (!empty($params[0])) {
            $this->setSource($params[0]);
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Source' parameter not provided."
            );
        }
        
        if (!empty($params[1])) {
            $this->setDestination($params[1]);
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Destination' parameter not provided."
            );
        }
        
        if (isset($params[2])) {
            $this->setIndexColumn(intval($params[2]));
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Index Column' parameter not provided."
            );
        }
        
        if (isset($params[3])) {
            $this->setFirstRowColumnNames(boolval($params[3]));
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'First Row Column Names' parameter not provided."
            );
        }
        
        // -- Initial report.
        $report = new Report(
            Report::TYPE_INFO,
            "Unknown status."
        );
        
        $report->add($this->beforeProcess());
        
        if ($report->contains(Report::TYPE_ERROR)) {
            $report->setType(Report::TYPE_ERROR);
            $report->setMessage("The script terminated with errors.");
            
            return $report;
        }
        
        // -- Deal with headers.
        if ($this->isFirstRowColumnNames()) {
            $headers = fgetcsv($this->getSourceFp());
            
            // Might return NULL or FALSE.
            if (empty($headers)) {
                $headers = [];
            }
            
            $this->setHeaders($headers);
            fputcsv($this->getDestinationFp(), $headers);
        }
        
        // -- Deal with reports.
        $report->add($this->index());
        
        // Clean rewind before processing.
        rewind($this->getSourceFp());
        $report->add($this->process());
        $report->add($this->afterProcess());
        
        if ($report->contains(Report::TYPE_ERROR)) {
            $report->setType(Report::TYPE_ERROR);
            $report->setMessage("The script terminated with errors.");
        } elseif ($report->contains(REPORT::TYPE_WARNING)) {
            $report->setType(Report::TYPE_WARNING);
            $report->setMessage("The script terminated with warnings.");
        } else {
            $report->setType(Report::TYPE_SUCCESS);
            $report->setMessage("The script terminated gracefully!");
        }
        
        return $report;
    }
    
    /**
     * Set the file header.
     * 
     * Stores the first row columns as the file header.
     * 
     * @param array $headers
     */
    protected function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
    
    /**
     * Get the file header.
     * 
     * Gets the file header as an array of strings.
     * 
     * @return array An array of strings.
     */
    protected function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Set the index column.
     * 
     * Sets the column to be indexed.
     * 
     * @param integer $indexColumn The numeric index of the column index. Index starts at 0.
     */
    protected function setIndexColumn($indexColumn)
    {
        $this->indexColumn = $indexColumn;
    }
    
    /**
     * Get the index column.
     * 
     * Gets the index of the column to be considered as the index. Index starts at 0.
     * 
     * @return integer
     */
    protected function getIndexColumn()
    {
        return $this->indexColumn;
    }
    
    /**
     * Set the path of the file to be read.
     * 
     * Sets the source path of the file. The source path can be either absolute or relative.
     * 
     * @param string $source
     */
    protected function setSource($source)
    {
        $this->source = $source;
    }
    
    /**
     * Get the path of the file to be read.
     * 
     * Gets the path of the file to be read. The path can be either absolute or relative.
     * 
     * @return string
     */
    protected function getSource()
    {
        return $this->source;
    }
    
    /**
     * Set the path of the destination file.
     * 
     * Sets the path of the file to be written. The path can be either absolute or relative.
     * 
     * @param string $destination
     */
    protected function setDestination($destination)
    {
        $this->destination = $destination;
    }
    
    /**
     * Get the path of the destination file.
     * 
     * Gets the path of the file to be written. The path can be either absolute or relative.
     * 
     * return string
     */
    protected function getDestination()
    {
        return $this->destination;
    }
    
    /**
     * Set the file handle of the source file.
     * 
     * Sets the file handle of the source file. The resource must be open and ready to be used.
     * 
     * @param resource $sourceFp A file handle.
     */
    protected function setSourceFp($sourceFp)
    {
        $this->sourceFp = $sourceFp;
    }
    
    /**
     * Get the file handle of the source file.
     * 
     * Gets the file handle of the source file. The resource will be open and ready to be used.
     * 
     * @return resource A file handle.
     */
    protected function getSourceFp()
    {
        return $this->sourceFp;
    }
    
    /**
     * Set the file handle of the destination file.
     * 
     * Sets the file handle of the destination file. The resource must be open and ready to be used.
     * 
     * @param resource $destinationFp A file handle.
     */
    protected function setDestinationFp($destinationFp)
    {
        $this->destinationFp = $destinationFp;
    }
    
    /**
     * Get the file handle of the destination file.
     * 
     * Gets the file handle of the destination file. The resource must be open and ready to be used.
     * 
     * @return resource
     */
    protected function getDestinationFp()
    {
        return $this->destinationFp;
    }
    
    /**
     * Set the Index.
     * 
     * Sets the index with $index. The array must contain unique keys representing the
     * indexed rows. The values will be arrays of positions (expressed in bytes) where to
     * find records identified by the index.
     * 
     * @param array $index
     */
    protected function setIndex(array $index)
    {
        $this->index = $index;
    }
    
    /**
     * Get the Index.
     * 
     * Sets the index with $index. The returned array contains unique keys representing the
     * indexed rows. The values are arrays of positions (expressed in bytes) where to
     * find records identified by the index.
     * 
     * @return array
     */
    protected function getIndex()
    {
        return $this->index;
    }
    
    /**
     * Set whether or not the first row contains the column names.
     * 
     * This method sets whether or not the first row of the source file contains the column names.
     * When set to true, the first row will be replicated in the destination file.
     * 
     * @param boolean $firstRowColumnNames
     */
    protected function setFirstRowColumnNames($firstRowColumnNames)
    {
        $this->firstRowColumnNames = $firstRowColumnNames;
    }
    
    /**
     * Whether or not the first row contains the column names.
     * 
     * This method returns whether or not the first row of the source file contains the column names.
     * When returning true, it means that the first row will be replicated in the destination file.
     * 
     * @return boolean
     */
    protected function isFirstRowColumnNames()
    {
        return $this->firstRowColumnNames;
    }
    
    /**
     * Set the parameters.
     * 
     * Set the initial parameters provided to the sript.
     * 
     * @param array $params
     */
    protected function setParams(array $params)
    {
        $this->params = $params;
    }
    
    /**
     * Get the parameters.
     * 
     * Gets the initial parameters provided to the script.
     * 
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }
    
    /**
     * Behaviour to be triggered at the beginning of the script.
     * 
     * This method contains the behaviours to be aplied at the very
     * beginning of the script. In this abstract class, it opens the source
     * and destination files. Implementors can override this method to add
     * additional behaviours.
     * 
     * @return \common_report_Report
     */
    protected function beforeProcess()
    {
        // -- Deal with file handling.
        $sourceFp = @fopen($this->getSource(), 'r');
        $destinationFp = @fopen($this->getDestination(), 'w');
        
        if ($sourceFp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Source file '" . $this->getSource() . "' could not be open."
            );
        } else {
            $this->setSourceFp($sourceFp);
        }
        
        if ($destinationFp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Destination file '" . $this->getDestination() . "' could not be open."
            );
        } else {
            $this->setDestinationFp($destinationFp);
            return new Report(
                Report::TYPE_SUCCESS,
                "Source and destination files open."
            );
        }
    }
    
    /**
     * Behaviour to be triggered at the end of the script.
     * 
     * This method contains the behaviours to be applied at the end
     * of the script. In this abstract class, it closes the source
     * and destination files. Implementors can override this method
     * to add additional behaviours.
     * 
     * @return \common_report_Report
     */
    protected function afterProcess()
    {
        @fclose($this->getSourceFp());
        @fclose($this->getDestinationFp());
        
        return new Report(
            Report::TYPE_INFO,
            "Source and Destination files closed."
        );
    }
    
    /**
     * Indexing method.
     * 
     * This method contains the logic to index the source file.
     * 
     * @return \common_report_Report
     */
    protected function index()
    {
        $index = [];
        $scanCount = $this->fillIndex($index, $this->getSourceFp());
        $this->setIndex($index);
        
        return new Report(
            Report::TYPE_INFO,
            $scanCount . " rows scanned for indexing. " . count($index) . " unique values indexed."
        );
    }
    
    protected function fillIndex(&$index, $sourceFp)
    {
        $indexColumn = $this->getIndexColumn();
        $scanCount = 0;
        
        rewind($sourceFp);
        
        if ($this->isFirstRowColumnNames()) {
            // Ignore first line in indexing.
            fgetcsv($sourceFp);
        }
        
        while (!feof($sourceFp)) {
            $position = ftell($sourceFp);
            $sourceData = fgetcsv($sourceFp);
            
            if (empty($sourceData)) {
                // End of file reached.
                break;
            }
            
            $scanCount++;
            
            if($sourceData !== false && !isset($sourceData[$indexColumn])){
                return new Report(
                    Report::TYPE_ERROR,
                    $indexColumn . " is not a valid offset for the source. It should be one of : ".implode(', ',array_keys($sourceData))
                );
            }
            
            $index[$sourceData[$indexColumn]][] = $position;
        }
        
        ksort($index);
        
        return $scanCount;
    }
    
    /**
     * Script processing logic.
     * 
     * This method has to be implemented by implementors. It contains
     * the logic to be applied on the source file, in order to produce
     * the destination file.
     * 
     * @return \common_report_Report
     */
    abstract protected function process();
}
