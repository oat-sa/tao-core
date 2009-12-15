<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/class.GenerisDataAdapter.php
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 01.12.2009, 15:49:12 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-includes begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-includes end

/* user defined constants */
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-constants begin
// section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C8F-constants end

/**
 * Short description of class tao_helpers_GenerisDataAdapter
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package tao
 * @subpackage helpers
 */
abstract class tao_helpers_GenerisDataAdapter
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute options
     *
     * @access protected
     * @var array
     */
    protected $options = array();

    /**
     * Short description of attribute DEFAULT_TOP_LEVEL_CLASS
     *
     * @access protected
     * @var string
     */
    const DEFAULT_TOP_LEVEL_CLASS = 'http://www.tao.lu/Ontologies/TAO.rdf#TAOObject';

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  array options
     * @return mixed
     */
    public function __construct($options = array())
    {
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C97 begin
		
		$this->options = $options;
		
        // section 127-0-1-1--464fd80f:12545a0876a:-8000:0000000000001C97 end
    }

    /**
     * Short description of method getOptions
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return array
     */
    public function getOptions()
    {
        $returnValue = array();

        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CC0 begin
		
		$returnValue = $this->options;
		
        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CC0 end

        return (array) $returnValue;
    }

    /**
     * Short description of method setOptions
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  array options
     * @return mixed
     */
    public function setOptions($options = array())
    {
        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CC3 begin
		
		$this->options = $options;
		
        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CC3 end
    }

    /**
     * Short description of method getClassProperties
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class clazz
     * @param  Class topLevelClazz
     * @return array
     */
    protected function getClassProperties( core_kernel_classes_Class $clazz,  core_kernel_classes_Class $topLevelClazz = null)
    {
        $returnValue = array();

        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CB8 begin
		
		if(is_null($topLevelClazz)){
			$topLevelClazz = new core_kernel_classes_Class(self::DEFAULT_TOP_LEVEL_CLASS);
		}
		
		$returnValue = $clazz->getProperties(false);
		if($clazz->uriResource == $topLevelClazz->uriResource){
			return (array) $returnValue;
		}
		$top = false;
		$parent = null;
		do{
			if(is_null($parent)){
				$parents = $clazz->getParentClasses(false);
			}
			else{
				$parents = $parent->getParentClasses(false);
			}
			if(count($parents) == 0){
				break;
			}
			
			foreach($parents as $parent){
				if( !($parent instanceof core_kernel_classes_Class) || is_null($parent)){
					$top = true; 
					break;
				}
				if($parent->uriResource == 'http://www.w3.org/2000/01/rdf-schema#Class'){
					continue;
				}
				
				$returnValue = array_merge($returnValue, $parent->getProperties(false));
				if($parent->uriResource == $topLevelClazz->uriResource){
					$top = true; 
					break;
				}
				
			}
		}while($top === false);
					
        // section 127-0-1-1-10bf4933:12549adcf20:-8000:0000000000001CB8 end

        return (array) $returnValue;
    }

    /**
     * Short description of method import
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string source
     * @param  Class destination
     * @return boolean
     */
    public abstract function import($source,  core_kernel_classes_Class $destination);

    /**
     * Short description of method export
     *
     * @abstract
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class source
     * @return string
     */
    public abstract function export( core_kernel_classes_Class $source);

} /* end of abstract class tao_helpers_GenerisDataAdapter */

?>