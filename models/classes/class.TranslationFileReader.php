<?php

error_reporting(E_ALL);

/**
 * A Reading class for TranslationFiles. Must be implemented by a concrete class
 * a given Translation Format such as XLIFF, PO, ... The read method must be
 * by subclasses.
 *
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage models_classes
 * @version 1.0
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034B0-includes begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034B0-includes end

/* user defined constants */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034B0-constants begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034B0-constants end

/**
 * A Reading class for TranslationFiles. Must be implemented by a concrete class
 * a given Translation Format such as XLIFF, PO, ... The read method must be
 * by subclasses.
 *
 * @abstract
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage models_classes
 * @version 1.0
 */
abstract class tao_models_classes_TranslationFileReader
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute filePath
     *
     * @access private
     * @var string
     */
    private $filePath = '';

    /**
     * Short description of attribute translationFile
     *
     * @access private
     * @var TranslationFile
     */
    private $translationFile = null;

    // --- OPERATIONS ---

    /**
     * Creates a new instance of TranslationFileReader.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string filePath
     * @return mixed
     */
    public function __construct($filePath)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034B7 begin
        $this->filePath = $filePath;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034B7 end
    }

    /**
     * Reads a translation file to put TranslationUnits of the TranslationFile
     * memory. Retrieved strings must be unescaped to avoid any misunderstanding
     * the client code. This method must be implemented by subclasses.
     *
     * @abstract
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public abstract function read();

    /**
     * Gets the TranslationFile instance resulting of the reading of the file.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return TranslationFile
     */
    public function getTranslationFile()
    {
        $returnValue = null;

        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034BC begin
        if ($this->getTranslationFile() != null) {
        	return $this->getTranslationFile();
        }
        else {
        	throw new tao_models_classes_TranslationException('No TranslationFile to retrieve.');
        }
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034BC end

        return $returnValue;
    }

    /**
     * Gets the location where the file has to be read.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getFilePath()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034BE begin
        return $this->filePath;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034BE end

        return (string) $returnValue;
    }

    /**
     * Sets the location where the file has to be read.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string filePath
     * @return mixed
     */
    public function setFilePath($filePath)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034C0 begin
        $this->filePath = $filePath;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:00000000000034C0 end
    }

    /**
     * Short description of method setTranslationFile
     *
     * @access protected
     * @author firstname and lastname of author, <author@example.org>
     * @param  TranslationFile translationFile
     * @return mixed
     */
    protected function setTranslationFile( TranslationFile $translationFile)
    {
        // section 10-13-1-85-39553493:1331c604ede:-8000:0000000000003542 begin
        // section 10-13-1-85-39553493:1331c604ede:-8000:0000000000003542 end
    }

} /* end of abstract class tao_models_classes_TranslationFileReader */

?>