<?php

error_reporting(E_ALL);

/**
 * Add more data to users grid here
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage models_grids_adaptors
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
// section 127-0-1-1--2e12219e:1360c8283db:-8000:0000000000003880-includes begin
// section 127-0-1-1--2e12219e:1360c8283db:-8000:0000000000003880-includes end

/* user defined constants */
// section 127-0-1-1--2e12219e:1360c8283db:-8000:0000000000003880-constants begin
// section 127-0-1-1--2e12219e:1360c8283db:-8000:0000000000003880-constants end

/**
 * Add more data to users grid here
 *
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage models_grids_adaptors
 */
class tao_models_grids_adaptors_UserAdditionalProperties
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
     * @param  string data
     * @return mixed
     */
    public function getValue($rowId, $columnId, $data = null)
    {
        $returnValue = null;

        // section 127-0-1-1--2e12219e:1360c8283db:-8000:0000000000003883 begin
		$returnValue = 'MyCountry';
        // section 127-0-1-1--2e12219e:1360c8283db:-8000:0000000000003883 end

        return $returnValue;
    }

} /* end of class tao_models_grids_adaptors_UserAdditionalProperties */

?>