<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/grid/Cell/class.Adapter.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 09.11.2011, 11:45:54 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_grid_Cell
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032AE-includes begin
// section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032AE-includes end

/* user defined constants */
// section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032AE-constants begin
// section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032AE-constants end

/**
 * Short description of class tao_helpers_grid_Cell_Adapter
 *
 * @abstract
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_grid_Cell
 */
abstract class tao_helpers_grid_Cell_Adapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute data
     *
     * @access protected
     * @var array
     */
    protected $data = array();

    /**
     * Short description of attribute options
     *
     * @access protected
     * @var array
     */
    protected $options = array();

    /**
     * Short description of attribute excludedProperties
     *
     * @access public
     * @var array
     */
    public $excludedProperties = array();

    // --- OPERATIONS ---

    /**
     * Short description of method getValue
     *
     * @abstract
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  string rowId
     * @param  string columnId
     * @param  string data
     * @return mixed
     */
    public abstract function getValue($rowId, $columnId, $data = null);

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032EA begin
		$this->options = $options;
		$this->excludedProperties = (is_array($this->options) && isset($this->options['excludedProperties'])) ? $this->options['excludedProperties'] : array();
        // section 127-0-1-1--17d909f0:1336f22bf6e:-8000:00000000000032EA end
    }

} /* end of abstract class tao_helpers_grid_Cell_Adapter */

?>