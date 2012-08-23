<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/class.Javascript.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 23.08.2012, 17:55:26 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B91-includes begin
// section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B91-includes end

/* user defined constants */
// section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B91-constants begin
// section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B91-constants end

/**
 * Short description of class tao_helpers_Javascript
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Javascript
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * converts a php array into a js array
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array array
     * @return string
     */
    public static function buildObject($array)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B92 begin
        if (count($array) == 0) {
			$returnValue = '{}';
        } else {
			$returnValue = '{';
			foreach ($array as $k => $v) {
				$returnValue .= '\''.$k.'\':'.(is_array($v) ? self::buildObject($v): '\''.$v.'\'').',';
			}
        }
		$returnValue =  substr($returnValue, 0, -1).'}';
        // section 127-0-1-1-34a0f4d4:13954298107:-8000:0000000000003B92 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Javascript */

?>