<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/class.Cli.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 17.02.2011, 15:10:18 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D70-includes begin
// section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D70-includes end

/* user defined constants */
// section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D70-constants begin
// section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D70-constants end

/**
 * Short description of class tao_helpers_Cli
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Cli
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute colors
     *
     * @access private
     * @var array
     */
    private static $colors = array(
'background' => array(
	'black' 		=> '40',
	'red' 			=> '41',
	'green' 		=> '42',
	'yellow' 		=> '43',
	'blue' 			=> '44',
	'magenta' 		=> '45',
	'cyan' 			=> '46',
	'light_gray'	=> '47'
),
'foreground' => array(
	'black' 		=> '0;30',
	'dark_gray' 	=> '1;30',	
	'blue' 			=> '0;34',
	'light_blue'	=> '1;34',
	'green' 		=> '0;32',
	'light_green' 	=> '1;32',
	'cyan' 			=> '0;36',
	'light_cyan' 	=> '1;36',
	'red' 			=> '0;31',
	'light_red' 	=> '1;31',
	'purple' 		=> '0;35',
	'light_purple' 	=> '1;35',
	'brown' 		=> '0;33',
	'yellow' 		=> '1;33',
	'light_gray' 	=> '0;37',
	'white' 		=> '1;37'
));

    // --- OPERATIONS ---

    /**
     * Short description of method getBgColor
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string name
     * @return string
     */
    public static function getBgColor($name)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D71 begin
        
        if(!empty($name) && array_key_exists($name, self::$colors['background'])){
        	$returnValue = self::$colors['background'][$name];
        }
        
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D71 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getFgColor
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string name
     * @return string
     */
    public static function getFgColor($name)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D75 begin
        
    	if(!empty($name) && array_key_exists($name, self::$colors['foreground'])){
        	$returnValue = self::$colors['foreground'][$name];
        }
        
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D75 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_Cli */

?>