<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/elements/xhtml/class.Free.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 28.10.2009, 15:39:51 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_Free
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/helpers/form/elements/class.Free.php');

/* user defined includes */
// section 127-0-1-1--6954f75c:1249b8f0f93:-8000:0000000000001ACE-includes begin
// section 127-0-1-1--6954f75c:1249b8f0f93:-8000:0000000000001ACE-includes end

/* user defined constants */
// section 127-0-1-1--6954f75c:1249b8f0f93:-8000:0000000000001ACE-constants begin
// section 127-0-1-1--6954f75c:1249b8f0f93:-8000:0000000000001ACE-constants end

/**
 * Short description of class tao_helpers_form_elements_xhtml_Free
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers_form_elements_xhtml
 */
class tao_helpers_form_elements_xhtml_Free
    extends tao_helpers_form_elements_Free
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method render
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return string
     */
    public function render()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--6954f75c:1249b8f0f93:-8000:0000000000001ACF begin
		
		$returnValue = $this->value;
		
        // section 127-0-1-1--6954f75c:1249b8f0f93:-8000:0000000000001ACF end

        return (string) $returnValue;
    }

} /* end of class tao_helpers_form_elements_xhtml_Free */

?>