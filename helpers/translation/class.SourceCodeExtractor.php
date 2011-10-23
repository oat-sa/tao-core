<?php

error_reporting(E_ALL);

/**
 * A TranslationExtractor that focuses on the extraction of Translation Units
 * source code. It searches for calls to the __() function. The generated
 * units will get the first parameter of the __() function as their source.
 *
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * A TranslationExtractor instance extracts TranslationUnits from a given source
 * as an Item, source code, ...
 *
 * @author Jerome Bogaerts
 * @since 2.2
 * @version 1.0
 */
require_once('tao/helpers/translation/class.TranslationExtractor.php');

/* user defined includes */
// section -64--88-1-7-3ec47102:13332ada7cb:-8000:0000000000003201-includes begin
// section -64--88-1-7-3ec47102:13332ada7cb:-8000:0000000000003201-includes end

/* user defined constants */
// section -64--88-1-7-3ec47102:13332ada7cb:-8000:0000000000003201-constants begin
// section -64--88-1-7-3ec47102:13332ada7cb:-8000:0000000000003201-constants end

/**
 * A TranslationExtractor that focuses on the extraction of Translation Units
 * source code. It searches for calls to the __() function. The generated
 * units will get the first parameter of the __() function as their source.
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */
class tao_helpers_translation_SourceCodeExtractor
    extends tao_helpers_translation_TranslationExtractor
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute filesTypes
     *
     * @access private
     * @var array
     */
    private $filesTypes = array();

    // --- OPERATIONS ---

    /**
     * Short description of method extract
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public function extract()
    {
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:0000000000003209 begin
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:0000000000003209 end
    }

    /**
     * Short description of method recursiveSearch
     *
     * @access private
     * @author firstname and lastname of author, <author@example.org>
     * @param  string directory
     * @return array
     */
    private function recursiveSearch($directory)
    {
        $returnValue = array();

        // section -64--88-1-7-23b3662f:133330291f8:-8000:000000000000322E begin
        // section -64--88-1-7-23b3662f:133330291f8:-8000:000000000000322E end

        return (array) $returnValue;
    }

    /**
     * Creates a SourceCodeExtractor for a given set of paths. Only file
     * that matches an entry in the $fileTypes array will be processed.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array paths
     * @param  array fileTypes
     * @return mixed
     */
    public function __construct($paths, $fileTypes)
    {
        // section -64--88-1-7-23b3662f:133330291f8:-8000:0000000000003234 begin
        // section -64--88-1-7-23b3662f:133330291f8:-8000:0000000000003234 end
    }

    /**
     * Gets an array of file extensions that will be processed. It acts as a
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return array
     */
    public function getFileTypes()
    {
        $returnValue = array();

        // section -64--88-1-7-23b3662f:133330291f8:-8000:000000000000323E begin
        // section -64--88-1-7-23b3662f:133330291f8:-8000:000000000000323E end

        return (array) $returnValue;
    }

    /**
     * Sets an array that contains the extensions of files that have to be
     * during the invokation of the SourceCodeExtractor::extract method.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array fileTypes
     * @return mixed
     */
    public function setFileTypes($fileTypes)
    {
        // section -64--88-1-7-23b3662f:133330291f8:-8000:0000000000003240 begin
        // section -64--88-1-7-23b3662f:133330291f8:-8000:0000000000003240 end
    }

} /* end of class tao_helpers_translation_SourceCodeExtractor */

?>