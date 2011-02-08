<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/elements/template/class.Template.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 08.02.2011, 16:22:19 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_template
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_Template
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.Template.php');

/* user defined includes */
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004F32-includes begin
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004F32-includes end

/* user defined constants */
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004F32-constants begin
// section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004F32-constants end

/**
 * Short description of class tao_helpers_form_elements_template_Template
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_template
 */
class tao_helpers_form_elements_template_Template
    extends tao_helpers_form_elements_Template
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method render
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004F35 begin
        
        if(file_exists($this->path) && is_readable($this->path)){
        	
	        extract($this->variables);
	      
	        ob_start();
	        
	       include $this->path;
	        
	        $returnValue = ob_get_contents();
	        
	        ob_end_clean();
	        
	        //clean the extracted variables
	        foreach($this->variables as $key => $name){
	        	unset($$key);
	        }
        	
        }
        
        // section 127-0-1-1--74f64380:12e057353c9:-8000:0000000000004F35 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_template_Template */

?>