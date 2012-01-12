<?php

error_reporting(E_ALL);

/**
 * A Translation Unit represents a single unit of translation of a software,
 * file, ... It has a source text in the original language and a target in which
 * text has to be translated.
 *
 * Example:
 * Source (English): The end is far away
 * Target (Yoda English): Far away the end is
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

/* user defined includes */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003478-includes begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003478-includes end

/* user defined constants */
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003478-constants begin
// section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003478-constants end

/**
 * A Translation Unit represents a single unit of translation of a software,
 * file, ... It has a source text in the original language and a target in which
 * text has to be translated.
 *
 * Example:
 * Source (English): The end is far away
 * Target (Yoda English): Far away the end is
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */
class tao_helpers_translation_TranslationUnit
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute source
     *
     * @access private
     * @var string
     */
    private $source = '';

    /**
     * Short description of attribute target
     *
     * @access private
     * @var string
     */
    private $target = '';

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

    // --- OPERATIONS ---

    /**
     * Gets the source text.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getSource()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000347F begin
        $returnValue = $this->source;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:000000000000347F end

        return (string) $returnValue;
    }

    /**
     * Gets the target text.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getTarget()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003481 begin
        $returnValue = $this->target;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003481 end

        return (string) $returnValue;
    }

    /**
     * Sets the source text.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string source
     * @return mixed
     */
    public function setSource($source)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003483 begin
        $this->source = $source;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003483 end
    }

    /**
     * Sets the target text.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string target
     * @return mixed
     */
    public function setTarget($target)
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003486 begin
        $this->target = $target;
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003486 end
    }

    /**
     * Creates a new instance of Translation Unit with specific source & target.
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string source
     * @param  string target
     * @return mixed
     */
    public function __construct($source, $target = "")
    {
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003489 begin
        $this->source = $source;
        $this->target = $target;
        
        // Default values for source and target languages are en-US.
        $this->setSourceLanguage('en-US');
        $this->setTargetLanguage('en-US');
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003489 end
    }

    /**
     * Short description of method setSourceLanguage
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string sourceLanguage
     * @return mixed
     */
    public function setSourceLanguage($sourceLanguage)
    {
        // section 10-13-1-85-4b6473d:1331c301495:-8000:000000000000351D begin
        $this->sourceLanguage = $sourceLanguage;
        // section 10-13-1-85-4b6473d:1331c301495:-8000:000000000000351D end
    }

    /**
     * Short description of method setTargetLanguage
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string targetLanguage
     * @return mixed
     */
    public function setTargetLanguage($targetLanguage)
    {
        // section 10-13-1-85-4b6473d:1331c301495:-8000:0000000000003520 begin
        $this->targetLanguage = $targetLanguage;
        // section 10-13-1-85-4b6473d:1331c301495:-8000:0000000000003520 end
    }

    /**
     * Short description of method getSourceLanguage
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getSourceLanguage()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-4b6473d:1331c301495:-8000:0000000000003523 begin
        $returnValue = $this->sourceLanguage;
        // section 10-13-1-85-4b6473d:1331c301495:-8000:0000000000003523 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getTargetLanguage
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return string
     */
    public function getTargetLanguage()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-4b6473d:1331c301495:-8000:0000000000003525 begin
        $returnValue = $this->targetLanguage;
        // section 10-13-1-85-4b6473d:1331c301495:-8000:0000000000003525 end

        return (string) $returnValue;
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

        // section 10-13-1-85--248fc0f4:133211c8937:-8000:0000000000003549 begin
        $returnValue = $this->getSourceLanguage() . '->' . $this->getTargetLanguage() . ':' .
        			   $this->getSource() . '-' . $this->getTarget();
        // section 10-13-1-85--248fc0f4:133211c8937:-8000:0000000000003549 end

        return (string) $returnValue;
    }

    /**
     * Short description of method hasSameTranslationUnitSource
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  TranslationUnit translationUnit
     * @return boolean
     */
    public function hasSameTranslationUnitSource( tao_helpers_translation_TranslationUnit $translationUnit)
    {
        $returnValue = (bool) false;

        // section -64--88-1-7-576a6b36:1333bcb6e9d:-8000:000000000000322F begin
        $returnValue = $this->getSource() == $translationUnit->getSource();
        // section -64--88-1-7-576a6b36:1333bcb6e9d:-8000:000000000000322F end

        return (bool) $returnValue;
    }

    /**
     * Short description of method hasSameTranslationUnitTarget
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  TranslationUnit translationUnit
     * @return boolean
     */
    public function hasSameTranslationUnitTarget( tao_helpers_translation_TranslationUnit $translationUnit)
    {
        $returnValue = (bool) false;

        // section -64--88-1-7-576a6b36:1333bcb6e9d:-8000:0000000000003232 begin
        $returnValue = $this->getTarget() == $translationUnit->getTarget();
        // section -64--88-1-7-576a6b36:1333bcb6e9d:-8000:0000000000003232 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_translation_TranslationUnit */

?>