<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/table/class.PropertyColumn.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 30.08.2012, 17:53:38 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_table
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_models_classes_table_Column
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/table/class.Column.php');

/* user defined includes */
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BB9-includes begin
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BB9-includes end

/* user defined constants */
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BB9-constants begin
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BB9-constants end

/**
 * Short description of class tao_models_classes_table_PropertyColumn
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_table
 */
class tao_models_classes_table_PropertyColumn
    extends tao_models_classes_table_Column
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute property
     *
     * @access public
     * @var Property
     */
    public $property = null;

    // --- OPERATIONS ---

    /**
     * Short description of method buildColumnFromArray
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array array
     * @return tao_models_classes_table_PropertyColumn
     */
    public static function buildColumnFromArray($array)
    {
        $returnValue = null;

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BF2 begin
        $returnValue = new static(new core_kernel_classes_Property($array['prop']));
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BF2 end

        return $returnValue;
    }

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Property property
     * @return mixed
     */
    public function __construct( core_kernel_classes_Property $property)
    {
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC8 begin
        $this->property = $property;
        parent::__construct($property->getLabel());
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC8 end
    }

    /**
     * Short description of method getProperty
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return core_kernel_classes_Property
     */
    public function getProperty()
    {
        $returnValue = null;

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC2 begin
        $returnValue = $this->property;
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC2 end

        return $returnValue;
    }

    /**
     * Short description of method getDataProvider
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return tao_models_classes_table_dataProvider
     */
    public function getDataProvider()
    {
        $returnValue = null;

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BD1 begin
        $returnValue = tao_models_classes_table_PropertyDP::singleton();
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BD1 end

        return $returnValue;
    }

    /**
     * Short description of method toArray
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function toArray()
    {
        $returnValue = array();

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BCF begin
        $returnValue = parent::toArray();
        $returnValue['prop'] = $this->property->getUri();
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BCF end

        return (array) $returnValue;
    }

} /* end of class tao_models_classes_table_PropertyColumn */

?>