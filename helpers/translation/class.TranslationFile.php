<?php

error_reporting(E_ALL);

/**
 * A translation file represents the translation of a file, software, item, ...
 * contains a list of Translation Units a source language and a target language.
 * File can be read and written by TranslationFileReader & TranslationFileWriter
 *
 * @author Jerome Bogaerts
 * @package tao
 * @see tao_model_classes_TranslationUnit
tao_model_classes_TranslationFileReader
tao_model_classes_TranslationFileWriter
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000348D-includes begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000348D-includes end

/* user defined constants */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000348D-constants begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000348D-constants end

/**
 * A translation file represents the translation of a file, software, item, ...
 * contains a list of Translation Units a source language and a target language.
 * File can be read and written by TranslationFileReader & TranslationFileWriter
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @see tao_model_classes_TranslationUnit
tao_model_classes_TranslationFileReader
tao_model_classes_TranslationFileWriter
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */
class tao_helpers_translation_TranslationFile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute sourceLanguage
     *
     * @access private
     * @var string
     */
    private $sourceLanguage = '';

    /**
     * Short description of attribute targetLanguage
     *
     * @access private
     * @var string
     */
    private $targetLanguage = '';

    /**
     * Short description of attribute translationUnits
     *
     * @access private
     * @var array
     */
    private $translationUnits = array();

    // --- OPERATIONS ---

    /**
     * Creates a new instance of TranslationFile for a specific source and
     * language.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string sourceLanguage
     * @param  string targetLanguage
     * @return mixed
     */
    public function __construct($sourceLanguage = "en-US", $targetLanguage = "en-US")
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003497 begin
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->translationUnits = array();
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003497 end
    }

    /**
     * Gets the source language.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getSourceLanguage()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000349B begin
        return $this->sourceLanguage;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000349B end

        return (string) $returnValue;
    }

    /**
     * Gets the target language.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getTargetLanguage()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000349D begin
        return $this->targetLanguage;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000349D end

        return (string) $returnValue;
    }

    /**
     * Gets the collection of Translation Units representing the
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return array
     */
    public function getTranslationUnits()
    {
        $returnValue = array();

        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000349F begin
        return $this->translationUnits;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000349F end

        return (array) $returnValue;
    }

    /**
     * Sets the source language.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string sourceLanguage
     * @return mixed
     */
    public function setSourceLanguage($sourceLanguage)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034A1 begin
        $this->sourceLanguage = $sourceLanguage;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034A1 end
    }

    /**
     * Sets the target language.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string targetLanguage
     * @return mixed
     */
    public function setTargetLanguage($targetLanguage)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034A4 begin
        $this->targetLanguage = $targetLanguage;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034A4 end
    }

    /**
     * Sets the collection of TranslationUnits representing the file.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array translationUnits
     * @return mixed
     */
    public function setTranslationUnits($translationUnits)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034A7 begin
        $this->translationUnits = $translationUnits;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034A7 end
    }

    /**
     * Adds a TranslationUnit instance to the file. It is appenned at the end of
     * collection.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  TranslationUnit translationUnit
     * @return mixed
     */
    public function addTranslationUnit( tao_helpers_translation_TranslationUnit $translationUnit)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034AA begin
        array_push($this->translationUnits, $translationUnit);
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034AA end
    }

    /**
     * Removes a given TranslationUnit from the collection of TranslationUnits
     * the file.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  TranslationUnit translationUnit
     * @return mixed
     */
    public function removeTranslationUnit( tao_helpers_translation_TranslationUnit $translationUnit)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034AD begin
        $tus = $this->getTranslationUnits();
        for ($i = 0; $i < count($tus); $i++) {
        	if ($tus[$i] === $translationUnit) {
        		unset($tus[$i]);
        		break;
        	}
        }
        
        throw new tao_helpers_translation_TranslationException('Cannot remove Translation Unit. Not Found.');
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034AD end
    }

    /**
     * Short description of method __toString
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function __toString()
    {
        $returnValue = (string) '';

        // section 10-13-1-85--248fc0f4:133211c8937:-8000:000000000000354B begin
    	$returnValue = $this->getSourceLanguage() . '->' . $this->getTargetLanguage() . ':';
        foreach ($this->getTranslationUnits() as $tu) {
        	$returnValue .= $tu;
        }
        // section 10-13-1-85--248fc0f4:133211c8937:-8000:000000000000354B end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_translation_TranslationFile */

?>