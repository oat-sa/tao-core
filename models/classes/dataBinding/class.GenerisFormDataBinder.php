<?php

error_reporting(E_ALL);

/**
 * A specific Data Binder which binds data coming from a Generis Form Instance
 * the Generis persistent memory.
 *
 * If the target instance was not set, a new instance of the target class will
 * created to receive the data to be bound.
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage models_classes_dataBinding
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * A data binder focusing on binding a source of data to a generis instance
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 */
require_once('tao/models/classes/dataBinding/class.GenerisInstanceDataBinder.php');

/* user defined includes */
// section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003CB8-includes begin
// section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003CB8-includes end

/* user defined constants */
// section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003CB8-constants begin
// section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003CB8-constants end

/**
 * A specific Data Binder which binds data coming from a Generis Form Instance
 * the Generis persistent memory.
 *
 * If the target instance was not set, a new instance of the target class will
 * created to receive the data to be bound.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage models_classes_dataBinding
 */
class tao_models_classes_dataBinding_GenerisFormDataBinder
    extends tao_models_classes_dataBinding_GenerisInstanceDataBinder
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Simply bind data from a Generis Instance Form to a specific generis class
     * If the instance was not specified, the binding implementation will create
     * new instance of the target class and bind data to it.
     *
     * The array of the data to be bound must contain keys that are property
     * The repspective values can be either scalar or vector (array) values or
     * values.
     *
     * - If the element of the $data array is scalar, it is simply bound using
     * - If the element of the $data array is a vector, the property values are
     * with the values of the vector.
     * - If the element is an object, the binder will infer the best method to
     * it in the persistent memory, depending on its nature.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  array data An array of values where keys are Property URIs and values are either scalar, vector or object values.
     * @return mixed
     */
    public function bind($data)
    {
        $returnValue = null;

        // section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003CBA begin
        // section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003CBA end

        return $returnValue;
    }

} /* end of class tao_models_classes_dataBinding_GenerisFormDataBinder */

?>