<?php

error_reporting(E_ALL);

/**
 * TAO - tao\helpers\translation\class.POUtils.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 20.10.2011, 15:33:02 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_translation
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 10-13-1-85--20e08ece:13320c7798f:-8000:0000000000003545-includes begin
// section 10-13-1-85--20e08ece:13320c7798f:-8000:0000000000003545-includes end

/* user defined constants */
// section 10-13-1-85--20e08ece:13320c7798f:-8000:0000000000003545-constants begin
// section 10-13-1-85--20e08ece:13320c7798f:-8000:0000000000003545-constants end

/**
 * Short description of class tao_helpers_translation_POUtils
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 * @package tao
 * @subpackage helpers_translation
 */
class tao_helpers_translation_POUtils
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method sanitize
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  string string
     * @param  boolean reverse
     * @return string
     */
    public static function sanitize($string, $reverse = false)
    {
        $returnValue = (string) '';

        // section 10-13-1-85--20e08ece:13320c7798f:-8000:0000000000003546 begin
		if ($reverse) {
			$smap = array('"', "\n", "\t", "\r");
			$rmap = array('\\"', '\\n"' . "\n" . '"', '\\t', '\\r');
			$returnValue = (string) str_replace($smap, $rmap, $string);
		} else {
			$smap = array('/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\"/');
			$rmap = array('', "\n", "\r", "\t", '"');
			$returnValue = (string) preg_replace($smap, $rmap, $string);
		}
        // section 10-13-1-85--20e08ece:13320c7798f:-8000:0000000000003546 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_translation_POUtils */

?>