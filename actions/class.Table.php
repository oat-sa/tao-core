<?php
/*  
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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

/**
 * Results Controller provide actions performed from url resolution
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */
class tao_actions_Table extends tao_actions_TaoModule {

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct() {

        parent::__construct();
    }

    protected function getRootClass() {
    	throw new common_exception_Error('getRootClass should never be called');
    }
    /*
     * conveniance methods
     */
    
    protected  function getColumns($identifier) {
    	 if (!$this->hasRequestParameter($identifier)) {
    	 	throw new common_Exception('Missing parameter "'.$identifier.'" for getColumns()');
    	 }
    	 $columns = array();
    	 foreach ($this->getRequestParameter($identifier) as $array) {
    	 	$column = tao_models_classes_table_Column::buildColumnFromArray($array);
    	 	if (!is_null($column)) {
    	 		$columns[] = $column;
    	 	}
    	 }
    	 return $columns;
    }

    /**
     * get the main class
     * @return core_kernel_classes_Classes
     */
    public function index() {
    	$filter = $this->getRequestParameter('filter');
		$this->setData('filter', $filter);
		$this->setView('table/index.tpl', 'tao');
    }
    /**
     * Data provider for the table, returns json encoded data according to the parameter 
     * @author Bertrand Chevrier, <taosupport@tudor.lu>, 
     * 
     * @param type $format  json, csv
     */
    public function data($format ="json") {
    	
   	$filter =  $this->hasRequestParameter('filter') ? $this->getFilterState('filter') : array();
    	$columns = $this->hasRequestParameter('columns') ? $this->getColumns('columns') : array();
    	
    	$page = $this->getRequestParameter('page');
		$limit = $this->getRequestParameter('rows');
		$sidx = $this->getRequestParameter('sidx');
		$sord = $this->getRequestParameter('sord');
		$searchField = $this->getRequestParameter('searchField');
		$searchOper = $this->getRequestParameter('searchOper');
		$searchString = $this->getRequestParameter('searchString');
		$start = $limit * $page - $limit;
		
                $response = new stdClass();
		    //todo remove this dependency
        	$clazz = new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
		$results	= $clazz->searchInstances($filter, array ('recursive'=>true));
         
		$counti		= $clazz->countInstances($filter, array ('recursive'=>true));
		
           
                
		$dpmap = array();
		foreach ($columns as $column) {
			$dataprovider = $column->getDataProvider();
			$found = false;
			foreach ($dpmap as $k => $dp) {
				if ($dp['instance'] == $dataprovider) {
					$found = true;
					$dpmap[$k]['columns'][] = $column;
				} 
			}
			if (!$found) {
				$dpmap[] = array(
					'instance'	=> $dataprovider,
					'columns'	=> array(
						$column
					)
				);
			}
		}
		
		foreach ($dpmap as $arr) {
			$arr['instance']->prepare($results, $arr['columns']);
		}
		
		foreach($results as $result) {
			$cellData = array();
			foreach ($columns as $column) {
				$cellData[] = $column->getDataProvider()->getValue($result, $column);
			}
			$response->rows[] = array(
				'id' => $result->uriResource,
				'cell' => $cellData
			);
		}
		$response->page = $page;
		if ($limit!=0) {
		$response->total = ceil($counti / $limit);//$total_pages;
		}
		else
		{
		$response->total = 1;
		}
		$response->records = count($results);
                //PPL reminder todo, delegate tot he data provider
		switch ($format) {
                    case "csv":$encodedData = $this->dataToCsv($columns, $response->rows,';','"');
                        header('Set-Cookie: fileDownload=true'); //used by jquery file download to find out the download has been triggered ... 
                        setcookie("fileDownload","true", 0, "/");
                        header("Content-type: text/csv"); 
                        header('Content-Disposition: attachment; filename=Data.csv');
                    break;

                    default: $encodedData = json_encode($response);
                    break;
                }

                echo $encodedData;
    }
    
    /**
     * Returns a flat array with the list of column labels.
     * @param columns an array of column object including the property information and that is used within tao class.Table context
     */
    private function columnsToFlatArray($columns){
        $flatColumnsArray = array();
        foreach ($columns as $column){
            $flatColumnsArray[] = $column->label;
        }
        return $flatColumnsArray;
        }
     /**
     * @return string A csv file with the data table
     * @param columns an array of column objects including the property information and as it is used in the tao class.Table.php context
     */
    private function dataToCsv($columns, $rows, $delimiter, $enclosure){
       //opens a temporary stream rather than producing a file and get benefit of csv php helpers
        $handle = fopen('php://temp', 'r+');
        //print_r($this->columnsToFlatArray($columns));
       fputcsv($handle, $this->columnsToFlatArray($columns), $delimiter, $enclosure);
       foreach ($rows as $line) {
	   $seralizedData = array();
	   foreach ($line["cell"] as $cellData){
	       $seralizedData[] = $this->cellDataToString($cellData);
	   }
           fputcsv($handle, $seralizedData, $delimiter, $enclosure);
       }
       rewind($handle);
       //read the content of the csv
       $encodedData = "";
       while (!feof($handle)) {
       $encodedData .= fread($handle, 8192);
       }
       fclose($handle);
       return $encodedData;
    }
    /**todo ppl delegate this to the dataprovider impleemntation
     *  Convenience function that attempts to support cases where the data provider set complex data objects into cellvalues
     * @return (string)
     */
    private function cellDataToString($cellData, $pieceDelimiters = array("", '     ') ){
	$strCellData = "";$currentDelimiter = array_shift($pieceDelimiters);
	//return serialize($cellData);
	if (is_array($cellData)) {
	    $last = array_pop(array_keys($cellData));
	    foreach ($cellData as $key => $cellDataPiece){
		if (isset($cellDataPiece[0]) and (is_array($cellDataPiece[0]))) {
		    $strCellData .= $this->cellDataToString($cellDataPiece, $pieceDelimiters);
		}
		else {
		    if (count($cellDataPiece)>1) {$strCellData .= implode($currentDelimiter, $cellDataPiece);} else {$strCellData .=array_pop($cellDataPiece);}
		    if ($key!=$last) {$strCellData.=array_shift($pieceDelimiters);}
		}
		
	    }
	}
	else {
	    if (is_object($cellData)) { $strCellData = serialize($cellData);}
	    else {
	    $strCellData = $cellData;
	    }
	}
	return $strCellData;
    }

    }


    
?>