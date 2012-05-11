<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/class.Label.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 11.05.2012, 17:19:58 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a FormElement entity
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/helpers/form/class.FormElement.php');

/* user defined includes */
// section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC2-includes begin
// section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC2-includes end

/* user defined constants */
// section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC2-constants begin
// section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC2-constants end

/**
 * Short description of class tao_helpers_form_elements_Label
 *
 * @abstract
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_Label
    extends tao_helpers_form_FormElement
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute widget
     *
     * @access public
     * @var string
     */
    public $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Label';

    // --- OPERATIONS ---

} /* end of abstract class tao_helpers_form_elements_Label */

?>