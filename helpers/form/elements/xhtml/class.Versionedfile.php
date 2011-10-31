<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/xhtml/class.Versionedfile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 11.10.2011, 14:01:52 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_Versionedfile
 *
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.Versionedfile.php');

/* user defined includes */
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F52-includes begin
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F52-includes end

/* user defined constants */
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F52-constants begin
// section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F52-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Versionedfile
 *
 * @access public
 * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Versionedfile
    extends tao_helpers_form_elements_Versionedfile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    public function getValue()
    {
        $returnValue = null;

        // section 127-0-1-1-3ba812e2:1284379704f:-8000:00000000000023F8 begin
        
        if(!is_null($this->value)){
        	$struct = @unserialize($this->value);
        	if($struct !== false){
        		$this->value = $struct;
        	}
        }
        $returnValue = $this->value;
        
        // section 127-0-1-1-3ba812e2:1284379704f:-8000:00000000000023F8 end

        return $returnValue;
    }
	
    /**
     * Short description of method render
     *
     * @access public
     * @author Cédric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F53 begin	

        $returnValue .= '<button value="click me" />';
        
        // section 127-0-1-1-1ae05cc0:132f22f86c1:-8000:0000000000003F53 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Versionedfile */

?>