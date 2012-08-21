<?php

error_reporting(E_ALL);

/**
 * TAO - tao/models/classes/cache/class.PartitionedCachable.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 21.08.2012, 11:34:14 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_cache
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_models_classes_Serializable
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/interface.Serializable.php');

/* user defined includes */
// section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036B1-includes begin
// section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036B1-includes end

/* user defined constants */
// section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036B1-constants begin
// section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036B1-constants end

/**
 * Short description of class tao_models_classes_cache_PartitionedCachable
 *
 * @abstract
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes_cache
 */
abstract class tao_models_classes_cache_PartitionedCachable
        implements tao_models_classes_Serializable
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute serial
     *
     * @access protected
     * @var string
     */
    protected $serial = '';

    /**
     * Short description of attribute serializedProperties
     *
     * @access protected
     * @var array
     */
    protected $serializedProperties = array();

    // --- OPERATIONS ---

    /**
     * Short description of method getSerial
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function getSerial()
    {
        $returnValue = (string) '';

        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003709 begin
        if(is_null($this->serial) || empty($this->serial)){
			$this->serial = $this->buildSerial();
		}
		$returnValue = $this->serial;
        // section 127-0-1-1--66865e2:1353e542706:-8000:0000000000003709 end

        return (string) $returnValue;
    }

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DC begin
        if (!is_null($this->getCache())) {
        	$this->getCache()->put($this);
        }
        // section 127-0-1-1-425ea117:1353e0d3541:-8000:00000000000036DC end
    }

    /**
     * Gives the list of attributes to serialize by reflection.
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function __sleep()
    {
        $returnValue = array();

        // section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036C1 begin
        $this->serializedProperties = array();
        $reflection = new ReflectionClass($this);
		foreach($reflection->getProperties() as $property){
			//assuming that private properties don't contain serializables
			if(!$property->isStatic() && !$property->isPrivate()) {
				$propertyName = $property->getName();
				$containsSerializable = false;
				$value = $this->$propertyName;
				if (is_array($value)) {
					$containsNonSerializable = false;
					$serials = array();
					foreach ($value as $key => $subvalue) {
						if (is_object($subvalue) && $subvalue instanceof self) {
							$containsSerializable = true; 
							$serials[$key] = $subvalue->getSerial();
						} else {
							$containsNonSerializable = true;
						}
					}
					if ($containsNonSerializable && $containsSerializable) {
						throw new common_exception_Error('Serializable '.$this->getSerial().' mixed serializable and non serializable values in property '.$propertyName);
					}
				} else {
					if (is_object($value) && $value instanceof self) {
						$containsSerializable = true;
						$serials = $value->getSerial();
					}
				}
				if ($containsSerializable) {
					$this->serializedProperties[$property->getName()] = $serials;
				} else {
					$returnValue[] = $property->getName();
				}
			}
		}
        // section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036C1 end

        return (array) $returnValue;
    }

    /**
     * Short description of method __wakeup
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function __wakeup()
    {
        // section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036C3 begin
		foreach ($this->serializedProperties as $key => $value) {
			if (is_array($value)) {
				$restored = array();
				foreach ($value as $arrayKey => $arrayValue) {
					$restored[$arrayKey] = $this->getCache()->get($arrayValue);
				}
			} else {
				$restored = $this->getCache()->get($value);
			}
			$this->$key = $restored;
		}
		$this->serializedProperties = array();
        // section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036C3 end
    }

    /**
     * Short description of method _remove
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function _remove()
    {
        // section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036CA begin
		//usefull only when persistance is enabled
		if(!is_null($this->getCache())){
			//clean session
			$this->getCache()->remove($this->getSerial());
		}
        // section 127-0-1-1-17e76cf9:1353916dbea:-8000:00000000000036CA end
    }

    /**
     * Short description of method getSuccessors
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function getSuccessors()
    {
        $returnValue = array();

        // section 127-0-1-1-3c671cea:1355e11f1c8:-8000:00000000000037BA begin
     	$reflection = new ReflectionClass($this);
		foreach($reflection->getProperties() as $property){
			if(!$property->isStatic() && !$property->isPrivate()){
				$propertyName = $property->getName();
				$value = $this->$propertyName;
				if (is_array($value)) {
					foreach ($value as $key => $subvalue) {
						if (is_object($subvalue) && $subvalue instanceof self) {
								$returnValue[] = $subvalue;
						}
					}
				} elseif (is_object($value) && $value instanceof self) {
						$returnValue[] = $value;
					}
				}
		}
        // section 127-0-1-1-3c671cea:1355e11f1c8:-8000:00000000000037BA end

        return (array) $returnValue;
    }

    /**
     * Short description of method getPredecessors
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string classFilter
     * @return array
     */
    public function getPredecessors($classFilter = null)
    {
        $returnValue = array();

        // section 127-0-1-1--275ea774:1356198dc72:-8000:00000000000037BC begin
		foreach ($this->getCache()->getAll() as $serial => $instance) {
			
			if (($classFilter == null || $instance instanceof $classFilter)
				&& in_array($this, $instance->getSuccessors())) {
				$returnValue[] = $instance;
				break;
			}
		}
        // section 127-0-1-1--275ea774:1356198dc72:-8000:00000000000037BC end

        return (array) $returnValue;
    }

    /**
     * create a unique serial number
     *
     * @abstract
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    protected abstract function buildSerial();

    /**
     * Short description of method getCache
     *
     * @abstract
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return tao_models_classes_cache_Cache
     */
    public abstract function getCache();

} /* end of abstract class tao_models_classes_cache_PartitionedCachable */

?>