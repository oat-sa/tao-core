<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/class.Versionedfile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 12.04.2012, 12:31:20 with ArgoUML PHP module 
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
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F4D-includes begin
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F4D-includes end

/* user defined constants */
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F4D-constants begin
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F4D-constants end

/**
 * Short description of class tao_helpers_form_elements_Versionedfile
 *
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
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
    public $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#VersionedFile';

    // --- OPERATIONS ---

} /* end of class tao_helpers_form_elements_Versionedfile */

?>