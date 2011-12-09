<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/class.File.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 09.12.2011, 16:08:45 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a FormElement entity
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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

    /**
     * Short description of attribute widget
     *
     * @access public
     * @var string
     */
    public $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#File';

    // --- OPERATIONS ---

} /* end of abstract class tao_helpers_form_elements_File */

?>