<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/grid/Cell/class.SubgridAdapter.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 10.11.2011, 15:48:22 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_grid_Cell
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_grid_Cell_Adapter
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 */
require_once('tao/helpers/grid/Cell/class.Adapter.php');

/* user defined includes */
// section 127-0-1-1-72bb438:1338cba5f73:-8000:0000000000003387-includes begin
// section 127-0-1-1-72bb438:1338cba5f73:-8000:0000000000003387-includes end

/* user defined constants */
// section 127-0-1-1-72bb438:1338cba5f73:-8000:0000000000003387-constants begin
// section 127-0-1-1-72bb438:1338cba5f73:-8000:0000000000003387-constants end

/**
 * Short description of class tao_helpers_grid_Cell_SubgridAdapter
 *
 * @abstract
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_grid_Cell
 */
abstract class tao_helpers_grid_Cell_SubgridAdapter
    extends tao_helpers_grid_Cell_Adapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute subgridClass
     *
     * @access public
     * @var string
     */
    public $subgridClass = '';

    /**
     * Short description of attribute subgridOptions
     *
     * @access public
     * @var array
     */
    public $subgridOptions = array();

    // --- OPERATIONS ---

    /**
     * Short description of method getValue
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  string data
     * @return mixed
     */
    public function getValue($rowId, $columnId, $data = null)
    {
        $returnValue = null;

        // section 127-0-1-1-72bb438:1338cba5f73:-8000:0000000000003388 begin
		if(isset($this->data[$rowId]) && is_a($this->data[$rowId], 'wfEngine_helpers_Monitoring_ActivityMonitoringGrid')){
			
			$returnValue = $this->data[$rowId];
			
		}else{

			$subgridClass = $this->subgridClass;
			$subgridData = $this->getSubgridRows($rowId);
			$subgridOptions = $this->subgridOptions;
			$subgrid = new $subgridClass($subgridData, $subgridOptions);
			if(is_a($subgrid, $this->subgridClass) && is_a($subgrid, 'tao_helpers_grid_GridContainer')){
				$returnValue = $subgrid;
			}else{
				throw new common_Exception('invalid subgrid class : '.$this->subgridClass);
			}

			$this->data[$rowId] = $returnValue;
			
		}
        // section 127-0-1-1-72bb438:1338cba5f73:-8000:0000000000003388 end

        return $returnValue;
    }

    /**
     * Short description of method getSubgridRows
     *
     * @abstract
     * @access protected
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string rowId
     * @return array
     */
    protected abstract function getSubgridRows($rowId);

    /**
     * Short description of method initSubgridOptions
     *
     * @access protected
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return mixed
     */
    protected function initSubgridOptions()
    {
        // section 127-0-1-1-72bb438:1338cba5f73:-8000:0000000000003399 begin
		$this->options = array();
        // section 127-0-1-1-72bb438:1338cba5f73:-8000:0000000000003399 end
    }

    /**
     * Short description of method initSubgridClass
     *
     * @abstract
     * @access protected
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  subgridClass
     * @return mixed
     */
    protected abstract function initSubgridClass($subgridClass = '');

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  array options
     * @param  string subgridClass
     * @return mixed
     */
    public function __construct($options = array(), $subgridClass = '')
    {
        // section 127-0-1-1-72bb438:1338cba5f73:-8000:00000000000033A8 begin
		parent::__construct($options);
		$this->initSubgridClass($subgridClass);
		$this->initSubgridOptions();
		if(!class_exists($this->subgridClass)){
			throw new Exception('the subgrid class does not exist : '.$this->subgridClass);
		}
        // section 127-0-1-1-72bb438:1338cba5f73:-8000:00000000000033A8 end
    }

    /**
     * Short description of method getSubgridColumnModel
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return array
     */
    public function getSubgridColumnModel()
    {
        $returnValue = array();

        // section 127-0-1-1-72bb438:1338cba5f73:-8000:00000000000033D0 begin
		$subgridClass = $this->subgridClass;
		$subgrid = new $subgridClass(array());//create empty subgrid to get its column model:
		if(is_a($subgrid, $this->subgridClass) && is_a($subgrid, 'tao_helpers_grid_GridContainer')){
			$returnValue = $subgrid->getGrid()->getColumnsModel();
		}else{
			throw new common_Exception('invalid subgrid class : '.$this->subgridClass);
		}
		
        // section 127-0-1-1-72bb438:1338cba5f73:-8000:00000000000033D0 end

        return (array) $returnValue;
    }

} /* end of abstract class tao_helpers_grid_Cell_SubgridAdapter */

?>