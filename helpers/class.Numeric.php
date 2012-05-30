<?php

error_reporting(E_ALL);

/**
 * Helper to process numeric input
 *
 * @author Jehan Bihin
 * @package tao
 * @since 2.2
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--35f5fbfd:1379d8c2a8c:-8000:0000000000003AB5-includes begin
// section 127-0-1-1--35f5fbfd:1379d8c2a8c:-8000:0000000000003AB5-includes end

/* user defined constants */
// section 127-0-1-1--35f5fbfd:1379d8c2a8c:-8000:0000000000003AB5-constants begin
// section 127-0-1-1--35f5fbfd:1379d8c2a8c:-8000:0000000000003AB5-constants end

/**
 * Helper to process numeric input
 *
 * @access public
 * @author Jehan Bihin
 * @package tao
 * @since 2.2
 * @subpackage helpers
 */
class tao_helpers_Numeric
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method parseFloat
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string value
     * @return float
     */
    public static function parseFloat($value)
    {
        $returnValue = (float) 0.0;

        // section 127-0-1-1--35f5fbfd:1379d8c2a8c:-8000:0000000000003AB6 begin
		$returnValue = str_replace(',', '.', $value);
		$returnValue = str_replace(' ', '', $returnValue);
		$p = strrpos($returnValue, '.');
		if ($p !== false) {
			$a = intval(str_replace('.', '', substr($returnValue, 0, $p)));
			$b = intval(substr($returnValue, $p + 1));
			$returnValue = floatval($a.'.'.$b);
		}
        // section 127-0-1-1--35f5fbfd:1379d8c2a8c:-8000:0000000000003AB6 end

        return (float) $returnValue;
    }

} /* end of class tao_helpers_Numeric */

?>