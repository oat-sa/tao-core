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
 * @subpackage models_classes
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
 * @subpackage models_classes
 * @version 1.0
 */
class tao_models_classes_TranslationUnit
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
        // section 10-13-1-85-72d0ca97:1331b62f595:-8000:0000000000003489 end
    }

} /* end of class tao_models_classes_TranslationUnit */

?>