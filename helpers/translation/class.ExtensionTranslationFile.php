<?php

error_reporting(E_ALL);

/**
 * A Translation File of a given TAO Extension.
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
 * A translation file represents the translation of a file, software, item, ...
 * contains a list of Translation Units a source language and a target language.
 * File can be read and written by TranslationFileReader & TranslationFileWriter
 *
 * @author Jerome Bogaerts
 * @see tao_model_classes_TranslationUnit
tao_model_classes_TranslationFileReader
tao_model_classes_TranslationFileWriter
 * @since 2.2
 * @version 1.0
 */
require_once('tao/helpers/translation/class.TranslationFile.php');

/* user defined includes */
// section -64--88-1-7-23b3662f:133330291f8:-8000:00000000000031F1-includes begin
// section -64--88-1-7-23b3662f:133330291f8:-8000:00000000000031F1-includes end

/* user defined constants */
// section -64--88-1-7-23b3662f:133330291f8:-8000:00000000000031F1-constants begin
// section -64--88-1-7-23b3662f:133330291f8:-8000:00000000000031F1-constants end

/**
 * A Translation File of a given TAO Extension.
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */
class tao_helpers_translation_ExtensionTranslationFile
    extends tao_helpers_translation_TranslationFile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute extensionName
     *
     * @access public
     * @var string
     */
    public $extensionName = '';

    // --- OPERATIONS ---

    /**
     * Creates a new instance of ExtensionTranslationFile.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string extensionName
     * @param  string sourceLanguage
     * @param  string targetLanguage
     * @return mixed
     */
    public function __construct($extensionName, $sourceLanguage = "en-US", $targetLanguage = "en-US")
    {
        // section -64--88-1-7-23b3662f:133330291f8:-8000:00000000000031F6 begin
        // section -64--88-1-7-23b3662f:133330291f8:-8000:00000000000031F6 end
    }

    /**
     * Gets the Extension name related to the translation file.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getExtensionName()
    {
        $returnValue = (string) '';

        // section -64--88-1-7-23b3662f:133330291f8:-8000:000000000000320F begin
        // section -64--88-1-7-23b3662f:133330291f8:-8000:000000000000320F end

        return (string) $returnValue;
    }

    /**
     * Sets the Extension name related to the translation file.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string extensionName
     * @return mixed
     */
    public function setExtensionName($extensionName)
    {
        // section -64--88-1-7-23b3662f:133330291f8:-8000:0000000000003211 begin
        // section -64--88-1-7-23b3662f:133330291f8:-8000:0000000000003211 end
    }

} /* end of class tao_helpers_translation_ExtensionTranslationFile */

?>