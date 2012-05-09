<?php

error_reporting(E_ALL);

/**
 * access operation for extensions
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
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A1C-includes begin
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A1C-includes end

/* user defined constants */
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A1C-constants begin
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A1C-constants end

/**
 * access operation for extensions
 *
 * @access public
 * @author Jehan Bihin
 * @package tao
 * @since 2.2
 * @subpackage models_classes_funcACL
 */
class tao_models_classes_funcACL_ExtensionAccessService
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
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A1E begin
		$uri = explode('#', $accessUri);
		list($type, $ext) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($roleUri);
		foreach ($rba[$ext] as $modn => $mod) {
			$roler->setPropertyValue(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), $this->makeEMAUri($ext, $modn));
			//Delete roles for actions
			foreach ($rba[$ext][$modn]['actions'] as $actn => $roles) {
				$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $modn, $actn)));
			}
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A1E end
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
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A22 begin
		$uri = explode('#', $accessUri);
		list($type, $ext) = explode('_', $uri[1]);
		$rba = tao_helpers_funcACL_funcACL::getRolesByActions();
		$roler = new core_kernel_classes_Class($roleUri);
		foreach ($rba[$ext] as $modn => $mod) {
			$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_MODULE_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $modn)));
			//Delete roles for actions
			foreach ($rba[$ext][$modn]['actions'] as $actn => $roles) {
				$roler->removePropertyValues(new core_kernel_classes_Property(PROPERTY_ACL_ACTION_GRANTACCESS), array('pattern' => $this->makeEMAUri($ext, $modn, $actn)));
			}
		}
		tao_helpers_funcACL_funcACL::removeRolesByActions();
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A22 end
    }

} /* end of class tao_models_classes_funcACL_ExtensionAccessService */

?>