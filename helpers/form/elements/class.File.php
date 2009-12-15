<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/elements/class.File.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 02.12.2009, 11:07:18 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a FormElement entity
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/class.FormElement.php');

/* user defined includes */
// section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CC5-includes begin
// section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CC5-includes end

/* user defined constants */
// section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CC5-constants begin
// section 127-0-1-1-3453b76:1254af40027:-8000:0000000000001CC5-constants end

/**
 * Short description of class tao_helpers_form_elements_File
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_File
    extends tao_helpers_form_FormElement
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute MAX_FILE_SIZE
     *
     * @access public
     * @var int
     */
    const MAX_FILE_SIZE = 2000000;

    // --- OPERATIONS ---

    /**
     * Short description of method render
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public abstract function render();

} /* end of abstract class tao_helpers_form_elements_File */

?>