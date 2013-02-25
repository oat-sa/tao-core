<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/data/class.StoredFileDescription.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 25.02.2013, 14:56:36 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_data
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The FileDescription data type contains all the data that a form collects or
 * about a file.
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 */
require_once('tao/helpers/form/data/class.FileDescription.php');

/* user defined includes */
// section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CF0-includes begin
// section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CF0-includes end

/* user defined constants */
// section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CF0-constants begin
// section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CF0-constants end

/**
 * Short description of class tao_helpers_form_data_StoredFileDescription
 *
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_data
 */
class tao_helpers_form_data_StoredFileDescription
    extends tao_helpers_form_data_FileDescription
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * The File instance in Generis Persistent Memory.
     *
     * @access private
     * @var File
     */
    private $file = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string uri The URI of the file stored by Generis.
     * @return mixed
     */
    public function __construct($uri)
    {
        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003D05 begin
        $this->file = new core_kernel_classes_File($uri);
        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003D05 end
    }

    /**
     * Short description of method getFile
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return core_kernel_classes_File
     */
    public function getFile()
    {
        $returnValue = null;

        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003D0B begin
        $returnValue = $this->file;
        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003D0B end

        return $returnValue;
    }

} /* end of class tao_helpers_form_data_StoredFileDescription */

?>