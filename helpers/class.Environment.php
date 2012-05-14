<?php

error_reporting(E_ALL);

/**
 * Utility class for server environment retrieval
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--19cc545a:133f9dd1f55:-8000:0000000000003442-includes begin
// section 127-0-1-1--19cc545a:133f9dd1f55:-8000:0000000000003442-includes end

/* user defined constants */
// section 127-0-1-1--19cc545a:133f9dd1f55:-8000:0000000000003442-constants begin
// section 127-0-1-1--19cc545a:133f9dd1f55:-8000:0000000000003442-constants end

/**
 * Utility class for server environment retrieval
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Environment
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Returns the maximum size for fileuploads in bytes
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return int
     */
    public static function getFileUploadLimit()
    {
        $returnValue = (int) 0;

        // section 127-0-1-1--19cc545a:133f9dd1f55:-8000:0000000000003443 begin
        $max_upload		= self::toBytes(ini_get('upload_max_filesize'));
        $max_post		= self::toBytes(ini_get('post_max_size'));
        $memory_limit	= self::toBytes(ini_get('memory_limit'));
        
        $returnValue = min($max_upload, $max_post, $memory_limit);        
        // section 127-0-1-1--19cc545a:133f9dd1f55:-8000:0000000000003443 end

        return (int) $returnValue;
    }

    /**
     * Returns the Operating System running TAO as a String.
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function getOperatingSystem()
    {
        $returnValue = (string) '';

        // section 10-13-1-85--245f9798:135a41f7e17:-8000:0000000000003832 begin
        $returnValue = PHP_OS;
        // section 10-13-1-85--245f9798:135a41f7e17:-8000:0000000000003832 end

        return (string) $returnValue;
    }

    /**
     * Short description of method toBytes
     *
     * @access private
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string phpSyntax
     * @return int
     */
    private static function toBytes($phpSyntax)
    {
        $returnValue = (int) 0;

        // section 127-0-1-1-209b93e2:1374a5ddd41:-8000:0000000000003A47 begin
        $val = trim($phpSyntax);
        	$last = strtolower($val[strlen($val)-1]);
        	switch($last) {
        		case 'g':
        			$val *= 1024;
        		case 'm':
        			$val *= 1024;
        		case 'k':
        			$val *= 1024;
        	}
        
        return $val;
        // section 127-0-1-1-209b93e2:1374a5ddd41:-8000:0000000000003A47 end

        return (int) $returnValue;
    }

} /* end of class tao_helpers_Environment */

?>