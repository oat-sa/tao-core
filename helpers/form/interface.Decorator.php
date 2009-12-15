<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/interface.Decorator.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 24.11.2009, 14:18:39 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a form. It provides the default behavior for form management and
 * be overridden for any rendering mode.
 * A form is composed by a set of FormElements.
 *
 * The form data flow is:
 * 1. add the elements to the form instance
 * 2. run evaluate (initElements, update states (submited, valid, etc), update
 * )
 * 3. render form
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/class.Form.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001951-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001951-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001951-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001951-constants end

/**
 * Short description of class tao_helpers_form_Decorator
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form
 */
interface tao_helpers_form_Decorator
{


    // --- OPERATIONS ---

    /**
     * Short description of method preRender
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function preRender();

    /**
     * Short description of method postRender
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function postRender();

    /**
     * Short description of method getOption
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string key
     * @return string
     */
    public function getOption($key);

    /**
     * Short description of method setOption
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string key
     * @param  string value
     * @return boolean
     */
    public function setOption($key, $value);

} /* end of interface tao_helpers_form_Decorator */

?>