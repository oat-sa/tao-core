<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/class.Display.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 05.11.2009, 13:58:12 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-5109b15:124a4877945:-8000:0000000000001AF8-includes begin
// section 127-0-1-1-5109b15:124a4877945:-8000:0000000000001AF8-includes end

/* user defined constants */
// section 127-0-1-1-5109b15:124a4877945:-8000:0000000000001AF8-constants begin
function _clean($input){
	return tao_helpers_Display::textCleaner($input);
}
// section 127-0-1-1-5109b15:124a4877945:-8000:0000000000001AF8-constants end

/**
 * Short description of class tao_helpers_Display
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Display
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method textCutter
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string input
     * @param  int maxLength
     * @return string
     */
    public static function textCutter($input, $maxLength = 75)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-5109b15:124a4877945:-8000:0000000000001AF9 begin
		
		if(strlen($input) > $maxLength){
			$input = "<span title='$input' style='cursor:pointer;'>".substr($input, 0, $maxLength)."[...]</span>";
		}
		$returnValue = $input;
		
        // section 127-0-1-1-5109b15:124a4877945:-8000:0000000000001AF9 end

        return (string) $returnValue;
    }

    /**
     * Short description of method textCleaner
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string input
     * @param  string joker
     * @return string
     */
    public static function textCleaner($input, $joker = '_')
    {
        $returnValue = (string) '';

        // section 127-0-1-1-3f9c691f:124c3973fb8:-8000:0000000000001B28 begin
		$i=0;
		while($i < strlen($input)){
			if(preg_match("/^[a-zA-Z0-9]{1}$/", $input[$i])){
				$returnValue .= $input[$i];
			}
			else{
				$returnValue .= $joker;
			}
			$i++;
		}
        // section 127-0-1-1-3f9c691f:124c3973fb8:-8000:0000000000001B28 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Display */

?>