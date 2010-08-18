<?php

error_reporting(E_ALL);

/**
 * Utility of display methods
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
 * Utility of display methods
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Display
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * enable you to cut a long string and end it with [...] and add an hover
     * to display the complete string on mouse over.
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string input
     * @param  int maxLength
     * @return string
     */
    public static function textCutter($input, $maxLength = 75)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-5109b15:124a4877945:-8000:0000000000001AF9 begin
		
		if(strlen($input) > $maxLength){
			$input = "<span title='$input' class='cutted' style='cursor:pointer;'>".substr($input, 0, $maxLength)."[...]</span>";
		}
		$returnValue = $input;
		
        // section 127-0-1-1-5109b15:124a4877945:-8000:0000000000001AF9 end

        return (string) $returnValue;
    }

    /**
     * clean a text with the joker character to replace any characters that is
     * alphanumeric
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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