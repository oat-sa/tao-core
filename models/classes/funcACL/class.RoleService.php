<?php

error_reporting(E_ALL);

/**
 * Func ACL roles services
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
 * This class provide the services for the Tao extension
 *
 * @author Jehan Bihin, <jehan.bihin@tudor.lu>
 */
require_once('tao/models/classes/class.TaoService.php');

/* user defined includes */
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039EF-includes begin
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039EF-includes end

/* user defined constants */
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039EF-constants begin
// section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039EF-constants end

/**
 * Func ACL roles services
 *
 * @access public
 * @author Jehan Bihin
 * @package tao
 * @since 2.2
 * @subpackage models_classes_funcACL
 */
class tao_models_classes_funcACL_RoleService
    extends tao_models_classes_TaoService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method add
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string name
     * @return string
     */
    public function add($name)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F1 begin
		$class = new core_kernel_classes_Class(CLASS_ROLE_BACKOFFICE);
		$instance = $class->createInstance($name, '');
		$instance->setPropertyValue(new core_kernel_classes_Property(RDF_SUBCLASSOF), CLASS_GENERIS_USER);
		$returnValue = $instance->uriResource;
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F1 end

        return (string) $returnValue;
    }

    /**
     * Short description of method edit
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string uri
     * @param  string name
     * @return mixed
     */
    public function edit($uri, $name)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F4 begin
		$instance = new core_kernel_classes_Resource($uri);
		$instance->setLabel($name);
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F4 end
    }

    /**
     * Short description of method remove
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string uri
     * @return mixed
     */
    public function remove($uri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F8 begin
		$instance = new core_kernel_classes_Resource($uri);
		$instance->delete();
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F8 end
    }

    /**
     * Short description of method attachUser
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string userUri
     * @param  string roleUri
     * @return mixed
     */
    public function attachUser($userUri, $roleUri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A01 begin
var_dump($userUri, $roleUri);
		$userRes = new core_kernel_classes_Resource($userUri);
		$userRes->setPropertyValue(new core_kernel_classes_Property(RDF_TYPE), $roleUri);
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A01 end
    }

    /**
     * Short description of method unattachUser
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string userUri
     * @param  string roleUri
     * @return mixed
     */
    public function unattachUser($userUri, $roleUri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A04 begin
		$userRes = new core_kernel_classes_Resource($userUri);
		$userRes->removePropertyValues(new core_kernel_classes_Property(RDF_TYPE), array('pattern' => $roleUri));
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A04 end
    }

    /**
     * Short description of method getRoles
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string userUri
     * @return array
     */
    public function getRoles($userUri)
    {
        $returnValue = array();

        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A07 begin
		$userRes = new core_kernel_classes_Resource($userUri);

		$rolesc = new core_kernel_classes_Class(CLASS_ROLE);
		foreach ($rolesc->getInstances(true) as $id => $r) {
			//$label = explode('#', $id);
			$nrole = array('id' => $id, 'label' => $r->getLabel(), 'selected' => false);
			//Selected
			foreach ($userRes->getTypes() as $uri => $t) {
				if ($uri == $id) $nrole['selected'] = true;
			}
			$returnValue[] = $nrole;
		}
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A07 end

        return (array) $returnValue;
    }

} /* end of class tao_models_classes_funcACL_RoleService */

?>