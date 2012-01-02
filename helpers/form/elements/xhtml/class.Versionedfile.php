<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/xhtml/class.Versionedfile.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 02.01.2012, 15:52:53 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_Versionedfile
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Versionedfile
    extends tao_helpers_form_elements_Versionedfile
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute evaluatedValue
     *
     * @access private
     * @var mixed
     */
    private $evaluatedValue = null;

    // --- OPERATIONS ---

    /**
     * Short description of method render
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
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

    /**
     * Short description of method getEvaluatedValue
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function getEvaluatedValue()
    {
        // section 127-0-1-1--19ea91f3:1349db91b83:-8000:00000000000060F3 begin
    	if(is_null($this->evaluatedValue) && !is_null($this->value)) {
    		$struct = @unserialize($this->value);
    		if($struct !== false){
    			$this->evaluatedValue = $struct;
    		} else {
    			common_Logger::w('Error unserialising VersionedFile Value', array(TAO));
    		}
    	}
    	return $this->evaluatedValue;
        // section 127-0-1-1--19ea91f3:1349db91b83:-8000:00000000000060F3 end
    }

} /* end of class tao_helpers_form_elements_xhtml_Versionedfile */

?>