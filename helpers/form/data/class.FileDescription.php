<?php

error_reporting(E_ALL);

/**
 * The FileDescription data type contains all the data that a form collects or
 * about a file.
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_data
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CC7-includes begin
// section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CC7-includes end

/* user defined constants */
// section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CC7-constants begin
// section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CC7-constants end

/**
 * The FileDescription data type contains all the data that a form collects or
 * about a file.
 *
 * @abstract
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage helpers_form_data
 */
abstract class tao_helpers_form_data_FileDescription
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * The name of the file e.g. thumbnail.png.
     *
     * @access private
     * @var string
     */
    private $name = '';

    /**
     * The size of the file in bytes.
     *
     * @access private
     * @var int
     */
    private $size = 0;

    // --- OPERATIONS ---

    /**
     * Creates a new instance of FileDescription.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string name The name of the file such as thumbnail.svg
     * @param  int size The size of the file in bytes.
     * @return mixed
     */
    public function __construct($name, $size)
    {
        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CD2 begin
        $this->name = $name;
        $this->size = $size;
        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CD2 end
    }

    /**
     * Returns the name of the file e.g. test.xml.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return string
     */
    public function getName()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CF2 begin
        $returnValue = $this->name;
        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CF2 end

        return (string) $returnValue;
    }

    /**
     * Returns the size of the file in bytes.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return int
     */
    public function getSize()
    {
        $returnValue = (int) 0;

        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CF5 begin
        $returnValue = $this->size;
        // section 127-0-1-1--2c821474:13d11698078:-8000:0000000000003CF5 end

        return (int) $returnValue;
    }

} /* end of abstract class tao_helpers_form_data_FileDescription */

?>