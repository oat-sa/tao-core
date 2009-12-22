<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 22.12.2009, 16:53:45 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_Validator
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.Validator.php');

/* user defined includes */
// section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CCF-includes begin
// section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CCF-includes end

/* user defined constants */
// section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CCF-constants begin
// section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CCF-constants end

/**
 * Short description of class tao_helpers_form_validators_FileMimeType
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_validators
 */
class tao_helpers_form_validators_FileMimeType
    extends tao_helpers_form_Validator
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CD0 begin
		
		parent::__construct($options);
		
		$this->message = __('Invalid file type!');
		if(!isset($this->options['mimetype'])){
			throw new Exception("Please define the mimetype option");
		}
		
        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CD0 end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    public function evaluate()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDA begin
		
		
		
		$mimetype = '';
		$value = $this->values[0];
		if(is_array($value)){
			
			$filename = $value['tmp_name'];

			//@todo in PHP >= 5.3.0 the finfo class has been moved from PECL to the core, so USE IT!
			
			//if the magic mime_content_type function is activated, usually by using the system mime magic translation ie. in /usr/share/mime.magic
			if (function_exists('mime_content_type') && ini_get('mime_magic.magicfile') && is_readable($filename)) {
	            $mimetype = mime_content_type($filename);
	        }
			else if(isset($value['type'])) {
				//by default use the $_FILE info but is a security failure 
	            $mimetype = $value['type'];
	        }
			if(!empty($mimetype) ){
				if(in_array($mimetype, $this->options['mimetype'])){
					$returnValue = true;
				}
				else{
					$this->message .= " ".implode(', ', $this->options['mimetype'])." are exected but $mimetype detected";
 				}
			}
		}
		
        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDA end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_FileMimeType */

?>