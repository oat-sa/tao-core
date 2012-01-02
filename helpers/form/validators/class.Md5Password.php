<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/validators/class.Md5Password.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 22.12.2011, 14:51:41 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_validators_Password
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * @author Joel Bout, <joel.bout@tudor.lu>
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
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  values
     * @return string
     */
    public function getValue($values)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D73 begin
		
		$returnValue = md5(parent::getRawValue());
		
        // section 127-0-1-1--d36e6ea:12597e82faa:-8000:0000000000001D73 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Md5Password */

?>