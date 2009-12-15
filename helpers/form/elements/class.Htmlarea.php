<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/elements/class.Htmlarea.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 30.09.2009, 14:21:07 with ArgoUML PHP module 
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
 * include tao_helpers_form_FormElement
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/class.FormElement.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000198C-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000198C-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000198C-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:000000000000198C-constants end

/**
 * Short description of class tao_helpers_form_elements_Htmlarea
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_Htmlarea
    extends tao_helpers_form_FormElement
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute widget
     *
     * @access protected
     * @var string
     */
    protected $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#HTMLArea';

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

} /* end of abstract class tao_helpers_form_elements_Htmlarea */

?>