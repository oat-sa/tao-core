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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/class.FileSourceService.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 15.02.2013, 14:03:05 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
require_once('tao/models/classes/class.GenerisService.php');

/* user defined includes */
// section 10-30-1--78-1b01f2ef:13ac03fd34f:-8000:0000000000003C03-includes begin
// section 10-30-1--78-1b01f2ef:13ac03fd34f:-8000:0000000000003C03-includes end

/* user defined constants */
// section 10-30-1--78-1b01f2ef:13ac03fd34f:-8000:0000000000003C03-constants begin
// section 10-30-1--78-1b01f2ef:13ac03fd34f:-8000:0000000000003C03-constants end

/**
 * Short description of class tao_models_classes_FileSourceService
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_FileSourceService
    extends tao_models_classes_GenerisService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getFileSourceClass
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @return core_kernel_classes_Class
     */
    public function getFileSourceClass()
    {
        $returnValue = null;

        // section 10-30-1--78--66279e2e:13bfb5107cf:-8000:0000000000003C9E begin
        $returnValue = new core_kernel_classes_Class(CLASS_GENERIS_VERSIONEDREPOSITORY);
        // section 10-30-1--78--66279e2e:13bfb5107cf:-8000:0000000000003C9E end

        return $returnValue;
    }

    /**
     * Short description of method addLocalSource
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param  string label
     * @param  string path
     * @return core_kernel_versioning_Repository
     */
    public function addLocalSource($label, $path)
    {
        $returnValue = null;

        // section 10-30-1--78-1b01f2ef:13ac03fd34f:-8000:0000000000003C05 begin
        $returnValue = core_kernel_fileSystem_FileSystemFactory::createFileSystem(
			new core_kernel_classes_Resource(INSTANCE_GENERIS_VCS_TYPE_LOCAL),
			'', '', '', $path, $label, true
		);
        // section 10-30-1--78-1b01f2ef:13ac03fd34f:-8000:0000000000003C05 end

        return $returnValue;
    }

    /**
     * Short description of method deleteFileSource
     *
     * @access public
     * @author Joel Bout, <joel@taotesting.com>
     * @param  Resource fileSource
     * @return boolean
     */
    public function deleteFileSource( core_kernel_classes_Resource $fileSource)
    {
        $returnValue = (bool) false;

        // section 10-30-1--78-1b01f2ef:13ac03fd34f:-8000:0000000000003C07 begin
        // section 10-30-1--78-1b01f2ef:13ac03fd34f:-8000:0000000000003C07 end

        return (bool) $returnValue;
    }

} /* end of class tao_models_classes_FileSourceService */

?>