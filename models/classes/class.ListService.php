<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/models/classes/class.ListService.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 12.04.2010, 12:38:57 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 127-0-1-1-68d242d4:127aa5da21a:-8000:000000000000234A-includes begin
// section 127-0-1-1-68d242d4:127aa5da21a:-8000:000000000000234A-includes end

/* user defined constants */
// section 127-0-1-1-68d242d4:127aa5da21a:-8000:000000000000234A-constants begin
// section 127-0-1-1-68d242d4:127aa5da21a:-8000:000000000000234A-constants end

/**
 * Short description of class tao_models_classes_ListService
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
class tao_models_classes_ListService
    extends tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute parentListClass
     *
     * @access protected
     * @var core_kernel_classes_Class
     */
    protected $parentListClass = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002364 begin
        
    	$this->parentListClass = new core_kernel_classes_Class(TAO_LIST_CLASS);
        
        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002364 end
    }

    /**
     * Short description of method getLists
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    public function getLists()
    {
        $returnValue = array();

        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:000000000000234C begin
        
        $returnValue[] = new core_kernel_classes_Class(GENERIS_BOOLEAN); 
        
        foreach($this->parentListClass->getSubClasses(false) as $list){
        	$returnValue[] = $list;
        }
        
        
        
        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:000000000000234C end

        return (array) $returnValue;
    }

    /**
     * Get a list class from the uri in parameter
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string uri
     * @return core_kernel_classes_Class
     */
    public function getList($uri)
    {
        $returnValue = null;

        // section 127-0-1-1-7add8745:127b99f9642:-8000:0000000000002388 begin
        // section 127-0-1-1-7add8745:127b99f9642:-8000:0000000000002388 end

        return $returnValue;
    }

    /**
     * Short description of method getListElements
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class listClass
     * @return array
     */
    public function getListElements( core_kernel_classes_Class $listClass)
    {
        $returnValue = array();

        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002359 begin
        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002359 end

        return (array) $returnValue;
    }

    /**
     * Short description of method removeList
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class listClass
     * @return boolean
     */
    public function removeList( core_kernel_classes_Class $listClass)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002366 begin
        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002366 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method removeListElement
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource element
     * @return boolean
     */
    public function removeListElement( core_kernel_classes_Resource $element)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002369 begin
        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002369 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method createList
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string label
     * @return core_kernel_classes_Class
     */
    public function createList($label = '')
    {
        $returnValue = null;

        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:000000000000236C begin
        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:000000000000236C end

        return $returnValue;
    }

    /**
     * Short description of method createListElement
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class listClass
     * @param  string label
     * @return core_kernel_classes_Resource
     */
    public function createListElement( core_kernel_classes_Class $listClass, $label = '')
    {
        $returnValue = null;

        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002374 begin
        // section 127-0-1-1-3fbbe8f5:127aa7fc0e0:-8000:0000000000002374 end

        return $returnValue;
    }

} /* end of class tao_models_classes_ListService */

?>