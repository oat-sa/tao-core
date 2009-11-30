<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/class.GenerisDataAdapter.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 30.11.2009, 18:13:29 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-includes begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-includes end

/* user defined constants */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-constants begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-constants end

/**
 * Short description of class tao_helpers_GenerisDataAdapter
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */
abstract class tao_helpers_GenerisDataAdapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute options
     *
     * @access protected
     * @var array
     */
    protected $options = array();

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C97 begin
		
		$this->options = $options;
		
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C97 end
    }

    /**
     * Short description of method import
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string source
     * @param  Class destination
     * @return boolean
     */
    public abstract function import($source,  core_kernel_classes_Class $destination);

    /**
     * Short description of method export
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class source
     * @return string
     */
    public abstract function export( core_kernel_classes_Class $source);

} /* end of abstract class tao_helpers_GenerisDataAdapter */

?>