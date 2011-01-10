<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/elements/xhtml/class.Label.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 24.02.2010, 10:34:04 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_Label
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.Label.php');

/* user defined includes */
// section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC6-includes begin
// section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC6-includes end

/* user defined constants */
// section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC6-constants begin
// section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC6-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Label
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Label
    extends tao_helpers_form_elements_Label
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

        // section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC8 begin
		
		if(isset($this->attributes['class'])){
			$classes = explode(' ', $this->attributes['class']);
			if(!isset($this->attributes['no-format'])){
				if(!in_array('form-elt-info', $classes)){
					$classes[] = 'form-elt-info';
				}
			}
			if(!in_array('form-elt-container', $classes)){
				$classes[] = 'form-elt-container';
			}
			$this->attributes['class'] = implode(' ', $classes);
		}
		else{
			if(isset($this->attributes['no-format'])){
				$this->attributes['class'] = 'form-elt-container';
			}
			else{
				$this->attributes['class'] = 'form-elt-info form-elt-container';
			}
		}
		unset($this->attributes['no-format']);
		
		$returnValue .= "<span class='form_desc'>";
		if(!empty($this->description)){
			$returnValue .=  _dh($this->getDescription());
		}
		$returnValue .= "</span>";
		$returnValue .= "<span ";
		$returnValue .= $this->renderAttributes();
		$returnValue .= " >"._dh($this->value)."</span>";
		
        // section 127-0-1-1-32598abc:126ff43e5cc:-8000:0000000000001EC8 end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Label */

?>