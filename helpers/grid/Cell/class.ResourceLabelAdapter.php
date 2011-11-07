<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/grid/Cell/class.ResourceLabelAdapter.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 07.11.2011, 12:17:29 with ArgoUML PHP module 
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
// section 127-0-1-1-6c609706:1337d294662:-8000:00000000000032F7-includes begin
// section 127-0-1-1-6c609706:1337d294662:-8000:00000000000032F7-includes end

/* user defined constants */
// section 127-0-1-1-6c609706:1337d294662:-8000:00000000000032F7-constants begin
// section 127-0-1-1-6c609706:1337d294662:-8000:00000000000032F7-constants end

/**
 * Short description of class tao_helpers_grid_Cell_ResourceLabelAdapter
 *
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_grid_Cell
 */
class tao_helpers_grid_Cell_ResourceLabelAdapter
    extends tao_helpers_grid_Cell_Adapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getValue
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  mixed data
     * @return mixed
     */
    public function getValue($rowId, $columnId, $data = null)
    {
        $returnValue = null;

        // section 127-0-1-1-6c609706:1337d294662:-8000:0000000000003331 begin
		if(!empty($data) && common_Utils::isUri($data)){
			$data = new core_kernel_classes_Resource($data);
		}
		if($data instanceof core_kernel_classes_Resource){
			$returnValue = $data->getLabel();
		}
        // section 127-0-1-1-6c609706:1337d294662:-8000:0000000000003331 end

        return $returnValue;
    }

} /* end of class tao_helpers_grid_Cell_ResourceLabelAdapter */

?>