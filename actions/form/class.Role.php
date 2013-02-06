<?php

error_reporting(E_ALL);

/**
 * TAO - tao\actions\form\class.Role.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 06.02.2013, 14:44:42 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Create a form from a  resource of your ontology. 
 * Each property will be a field, regarding it's widget.
 *
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 */
require_once('tao/actions/form/class.Instance.php');

/* user defined includes */
// section 10-13-1-85--332f5c64:13cafb9ead5:-8000:0000000000003C79-includes begin
// section 10-13-1-85--332f5c64:13cafb9ead5:-8000:0000000000003C79-includes end

/* user defined constants */
// section 10-13-1-85--332f5c64:13cafb9ead5:-8000:0000000000003C79-constants begin
// section 10-13-1-85--332f5c64:13cafb9ead5:-8000:0000000000003C79-constants end

/**
 * Short description of class tao_actions_form_Role
 *
 * @access public
 * @author Jerome Bogaerts, <jerome@taotesting.com>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Role
    extends tao_actions_form_Instance
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method initElements
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @return mixed
     */
    public function initElements()
    {
        // section 10-13-1-85--332f5c64:13cafb9ead5:-8000:0000000000003C7C begin
        parent::initElements();
        
        $encodedIncludesRolePropertyUri = tao_helpers_Uri::encode(PROPERTY_ROLE_INCLUDESROLE);
        $encodedInstanceUri = tao_helpers_Uri::encode($this->getInstance()->getUri());
        $rolesElement = $this->form->getElement($encodedIncludesRolePropertyUri);
        $rolesOptions = $rolesElement->getOptions();
        
        // remove the role itself in the list of includable roles
        // to avoid cyclic inclusions (even if the system supports it).
        if (array_key_exists($encodedInstanceUri, $rolesOptions)){
        	unset($rolesOptions[$encodedInstanceUri]);
        }
        
        $rolesElement->setOptions($rolesOptions);
        // section 10-13-1-85--332f5c64:13cafb9ead5:-8000:0000000000003C7C end
    }

} /* end of class tao_actions_form_Role */

?>