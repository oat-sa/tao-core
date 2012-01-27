<?php

error_reporting(E_ALL);

/**
 * TAO - tao/update/utils/class.Exception.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 02.09.2011, 14:23:00 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage update_utils
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055EC-includes begin
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055EC-includes end

/* user defined constants */
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055EC-constants begin
// section 127-0-1-1-170ecba2:13229fe0c97:-8000:00000000000055EC-constants end

/**
 * Short description of class tao_update_utils_Exception
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage update_utils
 */
class tao_update_utils_Exception extends Exception
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __toString
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     */
    public function __toString()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005606 begin
        $returnValue = $this->message;
        // section 127-0-1-1-170ecba2:13229fe0c97:-8000:0000000000005606 end

        return (string) $returnValue;
    }

} /* end of class tao_update_utils_Exception */

?>