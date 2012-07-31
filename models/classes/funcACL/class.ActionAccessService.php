<?php

error_reporting(E_ALL);

/**
 * access operation for actions
 *
 * @author Jehan Bihin
 * @package tao
 * @since 2.2
 * @subpackage models_classes_funcACL
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * mother class for access operations
 *
 * @author Jehan Bihin
 * @since 2.2
 */
require_once('tao/models/classes/funcACL/class.AccessService.php');

/* user defined includes */
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A3D-includes begin
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A3D-includes end

/* user defined constants */
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A3D-constants begin
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A3D-constants end

/**
 * access operation for actions
 *
 * @access public
 * @author Jehan Bihin
 * @package tao
 * @since 2.2
 * @subpackage models_classes_funcACL
 */
class tao_models_classes_funcACL_ActionAccessService
    extends tao_models_classes_funcACL_AccessService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method add
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string roleUri
     * @param  string accessUri
     * @return mixed
     */
    public function add($roleUri, $accessUri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A42 begin
		//$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($roleUri);
		$roler->setPropertyValue(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), $accessUri);
		tao_helpers_funcACL_funcACL::removeRolesByActions();
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A42 end
    }

    /**
     * Short description of method remove
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string roleUri
     * @param  string accessUri
     * @return mixed
     */
    public function remove($roleUri, $accessUri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A46 begin
		//$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($roleUri);
		$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), array('pattern' => $accessUri));
		tao_helpers_funcACL_funcACL::removeRolesByActions();
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A46 end
    }

    /**
     * Short description of method moduleToActionAccess
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string roleUri
     * @param  string accessUri
     * @return mixed
     */
    public function moduleToActionAccess($roleUri, $accessUri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A4A begin
		tao_helpers_funcACL_funcACL::removeRolesByActions();
		$uri = explode('#', $accessUri);
		list($type, $ext, $mod, $act) = explode('_', $uri[1]);
		$module = new core_kernel_classes_Resource($this->makeEMAUri($ext, $mod));
		$roler = new core_kernel_classes_Class($roleUri);
		$propa = new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS);
		$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $mod)));
		foreach (tao_helpers_funcACL_ActionModel::getActions($module) as $action) {
			if ($act != $action->getLabel()) $roler->setPropertyValue($propa, $this->makeEMAUri($ext, $mod, $action->getLabel()));
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A4A end
    }

    /**
     * Short description of method moduleToActionsAccess
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string roleUri
     * @param  string accessUri
     * @return mixed
     */
    public function moduleToActionsAccess($roleUri, $accessUri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A4C begin
		$uri = explode('#', $accessUri);
		list($type, $ext, $mod) = explode('_', $uri[1]);
		$module = new core_kernel_classes_Resource($accessUri);
		$roler = new core_kernel_classes_Class($roleUri);
		$propa = new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS);
		$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $mod)));
		foreach (tao_helpers_funcACL_ActionModel::getActions($module) as $action) {
			$roler->setPropertyValue($propa, $this->makeEMAUri($ext, $mod, $action->getLabel()));
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A4C end
    }

} /* end of class tao_models_classes_funcACL_ActionAccessService */

?>