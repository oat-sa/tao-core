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
		$response->total = ceil($counti / $limit);//$total_pages;
		$response->records = count($results);
                //PPL addition of different formats for the data, TODO  may be probelmatic is the buffer is already flushed with the header ...  
		switch ($format) {
                    case "csv":$encodedData = $this->dataToCsv($columns, $response->rows,';','"');
                        header('Set-Cookie: fileDownload=true'); //used by jquery file download to find out the download has been triggered ... 
                        setcookie("fileDownload","true", 0, "/");
                        header("Content-type: text/csv"); 
                        header('Content-Disposition: attachment; filename=TaoDeliveryResults.csv');
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
            //print_r($line);
           fputcsv($handle, $line["cell"], $delimiter, $enclosure);
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
    
    
    }
    
?>