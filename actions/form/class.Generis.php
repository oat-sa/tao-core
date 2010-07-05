<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/actions/form/class.Generis.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 05.07.2010, 16:04:35 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * This class provide a container for a specific form instance.
 * It's subclasses instanciate a form and it's elements to be used as a
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/class.FormContainer.php');

/* user defined includes */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248A-includes begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248A-includes end

/* user defined constants */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248A-constants begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248A-constants end

/**
 * Short description of class tao_actions_form_Generis
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
abstract class tao_actions_form_Generis
    extends tao_helpers_form_FormContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute DEFAULT_TOP_CLASS
     *
     * @access protected
     * @var string
     */
    const DEFAULT_TOP_CLASS = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';

    /**
     * Short description of attribute topClazz
     *
     * @access protected
     * @var Class
     */
    protected $topClazz = null;

    /**
     * Short description of attribute clazz
     *
     * @access protected
     * @var Class
     */
    protected $clazz = null;

    /**
     * Short description of attribute instance
     *
     * @access protected
     * @var Resource
     */
    protected $instance = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  Resource instance
     * @param  array options
     * @return mixed
     */
    public function __construct( core_kernel_classes_Class $clazz,  core_kernel_classes_Resource $instance = null, $options = array())
    {
        // section 127-0-1-1-ed0d875:129a2b8fa60:-8000:0000000000002060 begin

    	$this->clazz 	= $clazz;
    	$this->instance = $instance;
    	
    	if(isset($options['topClazz'])){
    		$this->topClazz = new core_kernel_classes_Class($options['topClazz']);
    		unset($options['topClazz']);
    	}
    	parent::__construct(array(), $options);
    	
        // section 127-0-1-1-ed0d875:129a2b8fa60:-8000:0000000000002060 end
    }

    /**
     * Short description of method getClazz
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return core_kernel_classes_Class
     */
    public function getClazz()
    {
        $returnValue = null;

        // section 127-0-1-1-ed0d875:129a2b8fa60:-8000:0000000000002067 begin
        
        $returnValue = $this->clazz;
        
        // section 127-0-1-1-ed0d875:129a2b8fa60:-8000:0000000000002067 end

        return $returnValue;
    }

    /**
     * Short description of method getInstance
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return core_kernel_classes_Resource
     */
    public function getInstance()
    {
        $returnValue = null;

        // section 127-0-1-1-ed0d875:129a2b8fa60:-8000:0000000000002069 begin
        
        $returnValue = $this->instance;
        
        // section 127-0-1-1-ed0d875:129a2b8fa60:-8000:0000000000002069 end

        return $returnValue;
    }

    /**
     * Short description of method getDefaultProperties
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return array
     */
    protected static function getDefaultProperties()
    {
        $returnValue = array();

        // section 127-0-1-1--7978326a:129a2dd1980:-8000:0000000000002070 begin
        
        $returnValue = array(
			new core_kernel_classes_Property(RDFS_LABEL)
		);
        
        // section 127-0-1-1--7978326a:129a2dd1980:-8000:0000000000002070 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getTopClazz
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return core_kernel_classes_Class
     */
    public function getTopClazz()
    {
        $returnValue = null;

        // section 127-0-1-1--7978326a:129a2dd1980:-8000:0000000000002089 begin
        
   	 	if(!is_null($this->topClazz)){
        	$returnValue = $this->topClazz;
        }
        else{
        	$returnValue = new core_kernel_classes_Class(self::DEFAULT_TOP_CLASS);
        }
        
        // section 127-0-1-1--7978326a:129a2dd1980:-8000:0000000000002089 end

        return $returnValue;
    }

} /* end of abstract class tao_actions_form_Generis */

?>