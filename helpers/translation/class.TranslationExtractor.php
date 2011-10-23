<?php

error_reporting(E_ALL);

/**
 * TAO - tao\helpers\translation\class.TranslationExtractor.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 23.10.2011, 23:47:06 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_translation
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031E0-includes begin
// section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031E0-includes end

/* user defined constants */
// section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031E0-constants begin
// section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031E0-constants end

/**
 * Short description of class tao_helpers_translation_TranslationExtractor
 *
 * @abstract
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_translation
 */
abstract class tao_helpers_translation_TranslationExtractor
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute paths
     *
     * @access private
     * @var array
     */
    private $paths = array();

    /**
     * Short description of attribute translationUnits
     *
     * @access private
     * @var array
     */
    private $translationUnits = array();

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array paths
     * @return mixed
     */
    public function __construct($paths)
    {
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031E7 begin
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031E7 end
    }

    /**
     * Short description of method extract
     *
     * @abstract
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return mixed
     */
    public abstract function extract();

    /**
     * Short description of method setPaths
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array paths
     * @return mixed
     */
    public function setPaths($paths)
    {
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031F1 begin
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031F1 end
    }

    /**
     * Short description of method getPaths
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return array
     */
    public function getPaths()
    {
        $returnValue = array();

        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031F4 begin
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031F4 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getTranslationUnits
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return array
     */
    public function getTranslationUnits()
    {
        $returnValue = array();

        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031F6 begin
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031F6 end

        return (array) $returnValue;
    }

    /**
     * Short description of method setTranslationUnits
     *
     * @access protected
     * @author firstname and lastname of author, <author@example.org>
     * @param  array translationUnits
     * @return mixed
     */
    protected function setTranslationUnits($translationUnits)
    {
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031FE begin
        // section -64--88-1-7-3ec47102:13332ada7cb:-8000:00000000000031FE end
    }

} /* end of abstract class tao_helpers_translation_TranslationExtractor */

?>