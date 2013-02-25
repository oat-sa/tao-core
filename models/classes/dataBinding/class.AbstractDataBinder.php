<?php

error_reporting(E_ALL);

/**
 * This abstract class represents a Data Binder that is able to bind data from a
 * source (e.g. a form) to another one (e.g. a persistent memory such as a
 *
 * Implementors have to implement the bind method to introduce their main logic
 * data binding.
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage models_classes_dataBinding
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003C99-includes begin
// section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003C99-includes end

/* user defined constants */
// section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003C99-constants begin
// section 127-0-1-1-2d2ef7de:13d10c8a117:-8000:0000000000003C99-constants end

/**
 * This abstract class represents a Data Binder that is able to bind data from a
 * source (e.g. a form) to another one (e.g. a persistent memory such as a
 *
 * Implementors have to implement the bind method to introduce their main logic
 * data binding.
 *
 * @abstract
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage models_classes_dataBinding
 */
abstract class tao_models_classes_dataBinding_AbstractDataBinder
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Data binding from the data representation passed as a parameter and the
     * data source.
     *
     * If the DataBinding fails, a DataBindingException is thrown.
     *
     * @abstract
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  array data An array of Data to bind from a data source to another.
     * @return mixed
     */
    public abstract function bind($data);

} /* end of abstract class tao_models_classes_dataBinding_AbstractDataBinder */

?>