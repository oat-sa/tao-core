<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao\helpers\form\elements\class.Calendar.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 20.01.2010, 10:15:10 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a FormElement entity
 *
 * @author firstname and lastname of author, <author@example.org>
 */
require_once('tao/helpers/form/class.FormElement.php');

/* user defined includes */
// section 10-13-1-39--1fd4bdcd:1264ade5709:-8000:0000000000001DB9-includes begin
// section 10-13-1-39--1fd4bdcd:1264ade5709:-8000:0000000000001DB9-includes end

/* user defined constants */
// section 10-13-1-39--1fd4bdcd:1264ade5709:-8000:0000000000001DB9-constants begin
// section 10-13-1-39--1fd4bdcd:1264ade5709:-8000:0000000000001DB9-constants end

/**
 * Short description of class tao_helpers_form_elements_Calendar
 *
 * @abstract
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_Calendar
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
    protected $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Calendar';

    // --- OPERATIONS ---

    /**
     * Short description of method render
     *
     * @abstract
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public abstract function render();

} /* end of abstract class tao_helpers_form_elements_Calendar */

?>