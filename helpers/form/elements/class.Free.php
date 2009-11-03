<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/elements/class.Free.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 02.11.2009, 12:38:10 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
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
// section 127-0-1-1-43da7667:1249b43a7f8:-8000:0000000000001AC7-includes begin
// section 127-0-1-1-43da7667:1249b43a7f8:-8000:0000000000001AC7-includes end

/* user defined constants */
// section 127-0-1-1-43da7667:1249b43a7f8:-8000:0000000000001AC7-constants begin
// section 127-0-1-1-43da7667:1249b43a7f8:-8000:0000000000001AC7-constants end

/**
 * Short description of class tao_helpers_form_elements_Free
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_Free
    extends tao_helpers_form_FormElement
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

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

} /* end of abstract class tao_helpers_form_elements_Free */

?>