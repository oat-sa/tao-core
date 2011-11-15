<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/grid/Cell/class.VersionedFileAdapter.php
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
 * include tao_helpers_grid_Cell_Adapter
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
require_once('tao/helpers/grid/Cell/class.Adapter.php');

/* user defined includes */
// section 127-0-1-1-6c609706:1337d294662:-8000:00000000000032F5-includes begin
// section 127-0-1-1-6c609706:1337d294662:-8000:00000000000032F5-includes end

/* user defined constants */
// section 127-0-1-1-6c609706:1337d294662:-8000:00000000000032F5-constants begin
// section 127-0-1-1-6c609706:1337d294662:-8000:00000000000032F5-constants end

/**
 * Short description of class tao_helpers_grid_Cell_VersionedFileAdapter
 *
 * @abstract
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers_grid_Cell
 */
abstract class tao_helpers_grid_Cell_VersionedFileAdapter
    extends tao_helpers_grid_Cell_Adapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getValue
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  string data
     * @return mixed
     */
    public function getValue($rowId, $columnId, $data = null)
    {
        $returnValue = null;

        // section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033CC begin
        
        $versionedFile = $this->getVersionedFile($rowId, $columnId, $data);
        $verison = $this->getVersion($rowId, $columnId, $data);
        $returnValue = array(
        	"uri" => $versionedFile->uriResource
        	, "version" => $version
        );
        
        // section 127-0-1-1--17a51322:133a2840e6a:-8000:00000000000033CC end

        return $returnValue;
    }

    /**
     * Short description of method getVersionedFile
     *
     * @abstract
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  string data
     * @return core_kernel_classes_Resource
     */
    public abstract function getVersionedFile($rowId, $columnId, $data = null);

    /**
     * Short description of method getVersion
     *
     * @abstract
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  string data
     * @return core_kernel_classes_Session_int
     */
    public abstract function getVersion($rowId, $columnId, $data = null);

} /* end of abstract class tao_helpers_grid_Cell_VersionedFileAdapter */

?>