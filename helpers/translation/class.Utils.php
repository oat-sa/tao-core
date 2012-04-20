<?php

error_reporting(E_ALL);

/**
 * Aims at providing common utility methods for the tao::helpers::translation
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
// section -64--88-56-1--1ef43195:136cfde50f6:-8000:0000000000003968-includes begin
// section -64--88-56-1--1ef43195:136cfde50f6:-8000:0000000000003968-includes end

/* user defined constants */
// section -64--88-56-1--1ef43195:136cfde50f6:-8000:0000000000003968-constants begin
// section -64--88-56-1--1ef43195:136cfde50f6:-8000:0000000000003968-constants end

/**
 * Aims at providing common utility methods for the tao::helpers::translation
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2
 * @subpackage helpers_translation
 * @version 1.0
 */
class tao_helpers_translation_Utils
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Returns the default language of TAO.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public static function getDefaultLanguage()
    {
        $returnValue = (string) '';

        // section -64--88-56-1--1ef43195:136cfde50f6:-8000:000000000000396D begin
        $returnValue = 'EN';
        // section -64--88-56-1--1ef43195:136cfde50f6:-8000:000000000000396D end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_translation_Utils */

?>