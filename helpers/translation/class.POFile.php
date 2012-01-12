<?php

error_reporting(E_ALL);

/**
 * TAO - tao\helpers\translation\class.POFile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 12.01.2012, 10:51:57 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_translation
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
// section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C6-includes begin
// section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C6-includes end

/* user defined constants */
// section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C6-constants begin
// section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038C6-constants end

/**
 * Short description of class tao_helpers_translation_POFile
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_POFile
    extends tao_helpers_translation_TranslationFile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute headers
     *
     * @access private
     * @var array
     */
    private $headers = array();

    // --- OPERATIONS ---

    /**
     * Short description of method addHeader
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string name
     * @param  string value
     * @return void
     */
    public function addHeader($name, $value)
    {
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038DA begin
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038DA end
    }

    /**
     * Short description of method removeHeader
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string name
     * @return void
     */
    public function removeHeader($name)
    {
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038DE begin
        // section 10-13-1-85-73c9aa2d:134d14a8b30:-8000:00000000000038DE end
    }

} /* end of class tao_helpers_translation_POFile */

?>