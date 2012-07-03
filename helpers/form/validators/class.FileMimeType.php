<?php

error_reporting(E_ALL);

/**
 * TAO - tao/helpers/form/validators/class.FileMimeType.php
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
 * The validators enable you to perform a validation callback on a form element.
 * It's provide a model of validation and must be overriden.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
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
 * @author Joel Bout, <joel.bout@tudor.lu>
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
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CD0 begin

		parent::__construct($options);

		$this->message = __('Invalid file type!');
		if(!isset($this->options['mimetype'])){
			throw new common_Exception("Please define the mimetype option for the FileMimeType Validator");
		}

        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CD0 end
    }

    /**
     * Short description of method evaluate
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  values
     * @return boolean
     */
    public function evaluate($values)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDA begin
		$mimetype = '';
		if (is_array($values)) {
			//if the magic mime_content_type function is activated, usually by using the system mime magic translation ie. in /usr/share/mime.magic
			if (isset($values['type'])) {
				//by default use the $_FILE info but is a security failure
				$mimetype = $values['type'];
			}
			if (isset($values['tmp_name']) && (empty($mimetype) || $mimetype == 'application/octet-stream')) {
				if (file_exists($values['tmp_name'])) {
					$mimetype = tao_helpers_File::getMimeType($values['tmp_name']);
				}
			}
			if (!empty($mimetype) ) {
				if (in_array($mimetype, $this->options['mimetype'])) {
					$returnValue = true;
				} else{
					$this->message .= " ".implode(', ', $this->options['mimetype'])." are expected but $mimetype detected";
				}
			}
		}
        // section 127-0-1-1-7214cdeb:1254e85ce09:-8000:0000000000001CDA end

        return (bool) $returnValue;
    }

} /* end of class tao_helpers_form_validators_FileMimeType */

?>