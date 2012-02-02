<?php

error_reporting(E_ALL);

/**
 * TAO - tao\helpers\translation\class.RDFTranslationUnit.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 02.02.2012, 10:09:38 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

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
 * @since 2.2
 * @version 1.0
 */
require_once('tao/helpers/translation/class.TranslationUnit.php');

/* user defined includes */
// section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A51-includes begin
// section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A51-includes end

/* user defined constants */
// section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A51-constants begin
// section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A51-constants end

/**
 * Short description of class tao_helpers_translation_RDFTranslationUnit
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_RDFTranslationUnit
    extends tao_helpers_translation_TranslationUnit
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute subject
     *
     * @access public
     * @var string
     */
    public $subject = '';

    /**
     * Short description of attribute predicate
     *
     * @access public
     * @var string
     */
    public $predicate = '';

    // --- OPERATIONS ---

    /**
     * Short description of method getSubject
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public function getSubject()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A59 begin
        // section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A59 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getPredicate
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public function getPredicate()
    {
        $returnValue = (string) '';

        // section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A5B begin
        // section 10-13-1-85-6e73505d:1353d49e194:-8000:0000000000003A5B end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_translation_RDFTranslationUnit */

?>