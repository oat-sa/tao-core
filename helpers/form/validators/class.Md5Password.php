<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 16.12.2009, 15:36:46 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_validators_Password
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/validators/class.Password.php');

/* user defined includes */
// section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D71-includes begin
// section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D71-includes end

/* user defined constants */
// section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D71-constants begin
// section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D71-constants end

/**
 * Short description of class tao_helpers_form_validators_Md5Password
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_Md5Password
    extends tao_helpers_form_validators_Password
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getValue
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function getValue()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D73 begin
		
		$returnValue = md5(parent::getValue());
		
        // section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D73 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Md5Password */

?>