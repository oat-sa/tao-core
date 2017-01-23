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
    
    public function __invoke($params)
    {
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
        }
        
        if (isset($params[3])) {
            $this->setFirstRowColumnNames(boolval($params[3]));
        }
        
        // -- Deal with file handling.
        $sourceFp = @fopen($this->getSource(), 'r');
        $destinationFp = @fopen($this->getDestination(), 'w');
        
        if ($sourceFp === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Source file '" . $this->getSource() . "' could not be open."
            );
        } else {
            $this->setSourceFp($sourceFp);
        }
        
        if ($destinationFp === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Destination file '" . $this->getDestination() . "' could not be open."
            );
        } else {
            $this->setDestinationFp($destinationFp);
        }
        
        // -- Deal with headers.
        if ($this->isFirstRowColumnNames()) {
            $headers = @fgetcsv($sourceFp);
            
            // Might return NULL or FALSE.
            if (empty($headers)) {
                $headers = [];
            }
            
            $this->setHeaders($headers);
        }
        
        // -- Deal with reports.
        $report = new Report(
            Report::TYPE_INFO,
            "Unknown status."
        );
        
        $report->add($this->index());
        $report->add($this->process());
        $report->add($this->afterProcess());
        
        if ($report->contains(Report::TYPE_ERROR)) {
            $report->setType(Report::TYPE_ERROR);
            $report->setMessage("The script terminated with errors.");
        } elseif ($report->contains(REPORT::TYPE_WARNING)) {
            $report->setType(Report::TYPE_WARNING);
            $report->setMessage("The script terminated with errors.");
        } else {
            $report->setType(Report::TYPE_SUCCESS);
            $report->setMessage("The script terminated gracefully!");
        }
        
        return $report;
    }
    
    protected function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
    
    protected function getHeaders()
    {
        return $this->headers;
    }
    
    protected function setIndexColumn($indexColumn)
    {
        $this->indexColumn = $indexColumn;
    }
    
    protected function getIndexColumn()
    {
        return $this->indexColumn;
    }
    
    protected function setSource($source)
    {
        $this->source = $source;
    }
    
    protected function getSource()
    {
        return $this->source;
    }
    
    protected function setDestination($destination)
    {
        $this->destination = $destination;
    }
    
    protected function getDestination()
    {
        return $this->destination;
    }
    
    protected function setSourceFp($sourceFp)
    {
        $this->sourceFp = $sourceFp;
    }
    
    protected function getSourceFp()
    {
        return $this->sourceFp;
    }
    
    protected function setDestinationFp($destinationFp)
    {
        $this->destinationFp = $destinationFp;
    }
    
    protected function getDestinationFp()
    {
        return $this->destinationFp;
    }
    
    protected function setIndex(array $index)
    {
        $this->index = $index;
    }
    
    protected function getIndex()
    {
        return $this->index;
    }
    
    protected function setFirstRowColumnNames($firstRowColumnNames)
    {
        $this->firstRowColumnNames = $firstRowColumnNames;
    }
    
    protected function isFirstRowColumnNames()
    {
        return $this->firstRowColumnNames;
    }
    
    protected function afterProcess()
    {
        @fclose($this->getSourceFp());
        @fclose($this->getDestinationFp());
        
        return new Report(
            Report::TYPE_INFO,
            "Source and Destination files closed."
        );
    }
    
    protected function index()
    {
        $index = [];
        $sourceFp = $this->getSourceFp();
        $destinationFp = $this->getDestinationFp();
        $indexColumn = $this->getIndexColumn();
        
        while (!feof($sourceFp)) {
            $position = ftell($sourceFp);
            $sourceData = fgetcsv($sourceFp);
            if($sourceData !== false && !isset($sourceData[$indexColumn])){
                return new Report(
                    Report::TYPE_ERROR,
                    $indexColumn . " is not a valid offset for the source. It should be one of : ".implode(', ',array_keys($sourceData))
                );
            }
            
            $index[$sourceData[$indexColumn]][] = $position;
        }
        
        ksort($index);
        $this->setIndex($index);
        
        return new Report(
            Report::TYPE_INFO,
            count($index) . " rows indexed."
        );
    }
    
    abstract protected function process();
}
