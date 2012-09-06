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

/**
 * Conveniance function
 * of Display::htmlize
 *
 * @param  string $input
 * @return string $output
 */
function _dh($input){
	return tao_helpers_Display::htmlize($input);
}

/**
 * Conveniance function
 * clean the input string (replace all no alphanum chars)
 * @param  string $input
 * @return string $output
 */
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

		if(mb_strlen($input) > $maxLength){
			$input = "<span title='$input' class='cutted' style='cursor:pointer;'>".mb_substr($input, 0, $maxLength, 'UTF-8')."[...]</span>";
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
        $randJoker = ($joker == '*');

		$i = 0;
		while($i < ((defined('TAO_DEFAULT_ENCODING')) ? mb_strlen($input, TAO_DEFAULT_ENCODING) : mb_strlen($input))){
			if(preg_match("/^[a-zA-Z0-9_-]{1}$/u", $input[$i])){
				$returnValue .= $input[$i];
			}
			else{
				if ($input[$i] == ' '){
					$returnValue .= '_';
				}
				else{
					$returnValue .= ((true === $randJoker) ? chr(rand(97, 122)) : $joker);
				}
			}
			$i++;
		}
        // section 127-0-1-1-3f9c691f:124c3973fb8:-8000:0000000000001B28 end

        return (string) $returnValue;
    }

    /**
     * Short description of method htmlize
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string input Central acces to display cleanly and more securly text into an  HTML page.
     * @return string
     */
    public static function htmlize($input)
    {
        $returnValue = (string) '';

        // section 127-0-1-1-eaea962:12d70d06717:-8000:0000000000002BB8 begin

        $returnValue = htmlentities($input, ENT_COMPAT, 'UTF-8');

        // section 127-0-1-1-eaea962:12d70d06717:-8000:0000000000002BB8 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Display */

?>