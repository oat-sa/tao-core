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
     * @return mixed
     */
    public function add($name)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F1 begin
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F1 end
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
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:00000000000039F8 end
    }

    /**
     * Short description of method attachUser
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string uri
     * @return mixed
     */
    public function attachUser($uri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A01 begin
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A01 end
    }

    /**
     * Short description of method unattachUser
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @param  string uri
     * @return mixed
     */
    public function unattachUser($uri)
    {
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A04 begin
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A04 end
    }

    /**
     * Short description of method getRoles
     *
     * @access public
     * @author Jehan Bihin, <jehan.bihin@tudor.lu>
     * @return array
     */
    public function getRoles()
    {
        $returnValue = array();

        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A07 begin
        // section 127-0-1-1--43b2a85f:1372be1e0be:-8000:0000000000003A07 end

        return (array) $returnValue;
    }

} /* end of class tao_models_classes_funcACL_RoleService */

?>