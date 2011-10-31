<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/class.Versionedfile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 11.10.2011, 14:01:52 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Represents a FormElement entity
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
require_once('tao/helpers/form/class.FormElement.php');

/* user defined includes */
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F4D-includes begin
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F4D-includes end

/* user defined constants */
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F4D-constants begin
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F4D-constants end

/**
 * Short description of class tao_helpers_form_elements_Versionedfile
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements
 */
class tao_helpers_form_elements_Versionedfile
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
    public $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Versionedfile';

    // --- OPERATIONS ---

} /* end of class tao_helpers_form_elements_Versionedfile */

?>