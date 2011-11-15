<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/grid/Cell/class.ResourceVersionedFileAdapter.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 14.11.2011, 17:49:54 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers_grid_Cell
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_grid_Cell_VersionedFileAdapter
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
require_once('tao/helpers/grid/Cell/class.VersionedFileAdapter.php');

/* user defined includes */
// section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033D8-includes begin
// section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033D8-includes end

/* user defined constants */
// section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033D8-constants begin
// section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033D8-constants end

/**
 * Short description of class tao_helpers_grid_Cell_ResourceVersionedFileAdapter
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers_grid_Cell
 */
class tao_helpers_grid_Cell_ResourceVersionedFileAdapter
    extends tao_helpers_grid_Cell_VersionedFileAdapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getVersionedFile
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  string data
     * @return core_kernel_classes_Resource
     */
    public function getVersionedFile($rowId, $columnId, $data = null)
    {
        $returnValue = null;

        // section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033DB begin
        if(empty($data)){
        	throw new Exception('data can not be empty');
        }
    	if(!empty($data) && common_Utils::isUri($data)){
			$data = new core_kernel_classes_Resource($data);
		}
		if(!core_kernel_versioning_File::isVersionedFile($data)){
			throw new Exception('data has to be a valid versioned file uri');
		}
		
		$returnValue = $data;
        // section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033DB end

        return $returnValue;
    }

    /**
     * Short description of method getVersion
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  string data
     * @return core_kernel_classes_Session_int
     */
    public function getVersion($rowId, $columnId, $data = null)
    {
        $returnValue = (int) 0;

        // section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033E1 begin
		$returnValue = null;
        // section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033E1 end

        return (int) $returnValue;
    }

} /* end of class tao_helpers_grid_Cell_ResourceVersionedFileAdapter */

?>