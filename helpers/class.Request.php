<?php

error_reporting(E_ALL);

/**
 * Utilities on requests
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A23-includes begin
// section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A23-includes end

/* user defined constants */
// section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A23-constants begin
// section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A23-constants end

/**
 * Utilities on requests
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Request
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Enables you to know if the request in the current scope is an ajax
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return boolean
     */
    public static function isAjax()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A24 begin
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
			if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				$returnValue = true;
			}
		}
        // section 127-0-1-1-2c289c37:12448d7d8c8:-8000:0000000000001A24 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_Request */

?>