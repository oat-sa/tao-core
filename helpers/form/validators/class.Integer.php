<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/validators/class.Integer.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 30.05.2012, 11:09:47 with ArgoUML PHP module
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_validators_Numeric
 *
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
 */
require_once('tao/helpers/form/validators/class.Numeric.php');

/* user defined includes */
// section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AAB-includes begin
// section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AAB-includes end

/* user defined constants */
// section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AAB-constants begin
// section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AAB-constants end

/**
 * Short description of class tao_helpers_form_validators_Integer
 *
 * @access public
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_Integer
    extends tao_helpers_form_validators_Numeric
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AAC begin
				parent::__construct($options);
        // section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AAC end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  values
     * @return boolean
     */
    public function evaluate($values)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AB6 begin
				if ($values == intval($values)) {
					$returnValue = parent::evaluate($values);
				} else {
					$returnValue = false;
					$this->message .= __('The value of this field must be an integer');
				}
        // section 127-0-1-1--4cd6560c:1379cf5f01c:-8000:0000000000003AB6 end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_Integer */

?>