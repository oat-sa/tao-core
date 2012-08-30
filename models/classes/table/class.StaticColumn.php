<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/table/class.StaticColumn.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 30.08.2012, 18:11:41 with ArgoUML PHP module 
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

/**
 * include tao_models_classes_table_dataProvider
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/table/interface.dataProvider.php');

/* user defined includes */
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BAD-includes begin
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BAD-includes end

/* user defined constants */
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BAD-constants begin
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BAD-constants end

/**
 * Short description of class tao_models_classes_table_StaticColumn
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_table
 */
class tao_models_classes_table_StaticColumn
    extends tao_models_classes_table_Column
        implements tao_models_classes_table_dataProvider
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute value
     *
     * @access public
     * @var string
     */
    public $value = '';

    // --- OPERATIONS ---

    /**
     * Short description of method prepare
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array resources
     * @param  array columns
     * @return mixed
     */
    public function prepare($resources, $columns)
    {
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BDA begin
        // nothing to do
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BDA end
    }

    /**
     * Short description of method getValue
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource resource
     * @param  Column column
     * @return string
     */
    public function getValue( core_kernel_classes_Resource $resource,  tao_models_classes_table_Column $column)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BDE begin
        $returnValue = $column->value;
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BDE end

        return (string) $returnValue;
    }

    /**
     * Short description of method buildColumnFromArray
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array array
     * @return tao_models_classes_table_StaticColumn
     */
    public static function buildColumnFromArray($array)
    {
        $returnValue = null;

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BF5 begin
        $returnValue = new self($array['label'], $array['val']);
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BF5 end

        return $returnValue;
    }

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string label
     * @param  string value
     * @return mixed
     */
    public function __construct($label, $value)
    {
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BAF begin
        parent::__construct($label);
        $this->value = $value;
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BAF end
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

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC0 begin
        $returnValue = parent::toArray();
        $returnValue['val'] = $this->value;
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BC0 end

        return (array) $returnValue;
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

        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BE4 begin
        $returnValue = $this;
        // section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000003BE4 end

        return $returnValue;
    }

} /* end of class tao_models_classes_table_StaticColumn */

?>