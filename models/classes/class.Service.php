<?php

error_reporting(E_ALL);

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001834-includes begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001834-includes end

/* user defined constants */
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001834-constants begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001834-constants end

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
abstract class tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * The defaults ontologies to load
     *
     * @access protected
     * @var array
     */
    protected $ontologies = array('http://www.tao.lu/Ontologies/generis.rdf','http://www.tao.lu/Ontologies/TAO.rdf');

    // --- OPERATIONS ---

    /**
     * constructor
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return void
     */
    public function __construct()
    {
        // section 10-13-1-45-792423e0:12398d13f24:-8000:000000000000183D begin
        // section 10-13-1-45-792423e0:12398d13f24:-8000:000000000000183D end
    }

    /**
     * Enable you to  load the RDF ontologies
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array ontologies
     * @return mixed
     */
    protected function loadOntologies($ontologies)
    {
        // section 127-0-1-1-266d5677:1246ba0ab68:-8000:0000000000001A9B begin
		
		$myOntologies = array_merge($this->ontologies, $ontologies);
		if(count($myOntologies) > 0){
			$session = core_kernel_classes_Session::singleton();
			foreach($myOntologies as $ontology){
				$session->model->loadModel($ontology);
			}
		}
		
        // section 127-0-1-1-266d5677:1246ba0ab68:-8000:0000000000001A9B end
    }

    /**
     * Retrieve a particular instance regarding the given parameters
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  string identifier
     * @param  string mode
     * @param  boolean ignoreCase
     * @return core_kernel_classes_Resource
     */
    public function getOneInstanceBy( core_kernel_classes_Class $clazz, $identifier = '', $mode = 'uri', $ignoreCase = false)
    {
        $returnValue = null;

        // section 10-13-1-45--20a3dc13:1239ebd775d:-8000:00000000000018B6 begin
		($ignoreCase) ? $identifier = strtolower(trim($identifier)) : $identifier = trim($identifier);
		foreach($clazz->getInstances() as $resource){
			if( strlen($identifier) > 0 ){
				$comparator = false;
				switch ($mode){
					case 'uri'		: $comparator 	= $resource->uriResource; 	break;
					case 'label'	: $comparator 	= $resource->getLabel(); 	break;
					default : throw new Exception("Unsupported mode $mode");
				}
				if($ignoreCase){
					$comparator = strtolower($comparator);
				}
				
				if( $identifier == $comparator && $comparator !== false ){
					$returnValue = $resource;
					break;
				}
			}
		}
        // section 10-13-1-45--20a3dc13:1239ebd775d:-8000:00000000000018B6 end

        return $returnValue;
    }

    /**
     * serach the instances matching the filters in parameters
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array propertyFilters
     * @param  Class topClazz
     * @param  array options
     * @return array
     */
    public function searchInstances($propertyFilters = array(),  core_kernel_classes_Class $topClazz = null, $options = array())
    {
        $returnValue = array();

        // section 127-0-1-1-106f2734:126b2f503d0:-8000:0000000000001E96 begin
		
		if(count($propertyFilters) == 0){
			return $returnValue; 
		}
		
		if(isset($options['lang'])){
			$langToken = " AND (l_language = '' OR l_language = '{$options['lang']}') ";
		}
		
		$query = "SELECT DISTINCT `subject` FROM `statements` WHERE ";
		
		$conditions = array();
		foreach($propertyFilters as $propUri => $pattern){
			if(is_string($pattern)){
				if(!empty($pattern)){
					$conditions[] = " (`predicate` = '{$propUri}' AND `object` LIKE '".str_replace('*', '%', $pattern)."' $langToken ) ";
				}
			}
			if(is_array($pattern)){
				if(count($pattern) > 0){
					$multiCondition =  " (`predicate` = '{$propUri}' AND  ";
					foreach($pattern as $i => $patternToken){
						if($i > 0){
							$multiCondition .= " OR ";
						}
						$multiCondition .= " `object` LIKE '".str_replace('*', '%', $patternToken)."'  ";
					}
					$conditions[] = "{$multiCondition} {$langToken} ) ";
				}
			}
		}
		if(count($conditions) == 0){
			return $returnValue; 
		}
		$matchingUris = array();
		
		$intersect = true; 
		if(isset($options['chaining'])){
			if($options['chaining'] == 'or'){
				$intersect = false; 
			}
		}
		
		$dbWrapper = core_kernel_classes_DbWrapper::singleton(DATABASE_NAME);
		if(count($conditions) > 0){
			$i = 0;
			foreach($conditions as $condition){
				$tmpMatchingUris = array();
				$result = $dbWrapper->execSql($query . $condition);
				while (!$result->EOF){
					$tmpMatchingUris[] = $result->fields['subject'];
					$result->MoveNext();
				}
				if($intersect){
					//EXCLUSIVES CONDITIONS
					if($i == 0){
						$matchingUris = $tmpMatchingUris;
					}
					else{
						$matchingUris = array_intersect($matchingUris, $tmpMatchingUris);
					}
				}
				else{
					//INCLUSIVES CONDITIONS
					$matchingUris = array_merge($matchingUris, $tmpMatchingUris);
				}
				$i++;
			}
		}
		
		if(!is_null($topClazz)){
			$instances = $topClazz->getInstances(true);
			foreach($matchingUris as $matchingUri){
				if(isset($instances[$matchingUri])){
					if(!in_array($instances[$matchingUri], $returnValue)){
						$returnValue[] = $instances[$matchingUri];
					}
				}
			}
		}
		
        // section 127-0-1-1-106f2734:126b2f503d0:-8000:0000000000001E96 end

        return (array) $returnValue;
    }

    /**
     * Retrieve a property
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  string label
     * @return core_kernel_classes_Property
     */
    public function getPropertyByLabel( core_kernel_classes_Class $clazz, $label)
    {
        $returnValue = null;

        // section 10-13-1-45-2836570e:123bd13e69b:-8000:000000000000187B begin
		if(strlen(trim($label)) == 0){
			throw new Exception("Please, never use empty labels!");
		}
		foreach($clazz->getProperties() as $property){
			if($property->getLabel() == $label){
				$returnValue = $property;
				break;
			}
		}
        // section 10-13-1-45-2836570e:123bd13e69b:-8000:000000000000187B end

        return $returnValue;
    }

    /**
     * Instantiate an RDFs Class
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  string label
     * @return core_kernel_classes_Resource
     */
    public function createInstance( core_kernel_classes_Class $clazz, $label = '')
    {
        $returnValue = null;

        // section 10-13-1-45--135fece8:123b76cb3ff:-8000:0000000000001897 begin
		if( empty($label) ){
			$label = $clazz->getLabel() . '_' . (count($clazz->getInstances()) + 1);
		}
		$returnValue = core_kernel_classes_ResourceFactory::create($clazz, $label, '');
        // section 10-13-1-45--135fece8:123b76cb3ff:-8000:0000000000001897 end

        return $returnValue;
    }

    /**
     * Subclass an RDFS Class
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class parentClazz
     * @param  string label
     * @return core_kernel_classes_Class
     */
    public function createSubClass( core_kernel_classes_Class $parentClazz, $label = '')
    {
        $returnValue = null;

        // section 127-0-1-1-404a280c:12475f095ee:-8000:0000000000001AB5 begin
		if( empty($label) ){
			$label = $parentClazz->getLabel() . '_' . (count($parentClazz->getInstances()) + 1);
		}
		$returnValue = $parentClazz->createSubClass($label, '');
		
        // section 127-0-1-1-404a280c:12475f095ee:-8000:0000000000001AB5 end

        return $returnValue;
    }

    /**
     * bind the given RDFS properties to the RDFS resource in parameter
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource instance
     * @param  array properties
     * @return core_kernel_classes_Resource
     */
    public function bindProperties( core_kernel_classes_Resource $instance, $properties = array())
    {
        $returnValue = null;

        // section 10-13-1-45--135fece8:123b76cb3ff:-8000:00000000000018A5 begin
		
		foreach($properties as $propertyUri => $propertyValue){
			
			$prop = new core_kernel_classes_Property( $propertyUri );
			$values = $instance->getPropertyValuesCollection($prop);
			if($values->count() > 0){
				if(is_array($propertyValue)){
					if($instance->removePropertyValues($prop)){
						foreach($propertyValue as $aPropertyValue){
							$instance->setPropertyValue(
								$prop,
								$aPropertyValue
							);
						}
					}
				}
				else{
					$instance->editPropertyValues(
						$prop,
						$propertyValue
					);
					if(strlen(trim($propertyValue))==0){
						//if the property value is an empty space(the default value in a select input field), delete the corresponding triplet (and not all property values)
						core_kernel_classes_ApiModelOO::singleton()->removeStatement($instance->uriResource, $propertyUri, '', '');
					}
				}
			}
			else{
				
				if(is_array($propertyValue)){
					foreach($propertyValue as $aPropertyValue){
						$instance->setPropertyValue(
							$prop,
							$aPropertyValue
						);
					}
				}
				else{
					$instance->setPropertyValue(
						$prop,
						$propertyValue
					);
				}
			}
		}
        $returnValue = $instance;
		
        // section 10-13-1-45--135fece8:123b76cb3ff:-8000:00000000000018A5 end

        return $returnValue;
    }

    /**
     * duplicate a resource
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource instance
     * @param  Class clazz
     * @return core_kernel_classes_Resource
     */
    public function cloneInstance( core_kernel_classes_Resource $instance,  core_kernel_classes_Class $clazz = null)
    {
        $returnValue = null;

        // section 127-0-1-1-50de96c6:1266ae198e7:-8000:0000000000001E30 begin
		
		$returnValue = $this->createInstance($clazz);
		if(!is_null($returnValue)){
			foreach($clazz->getProperties(true) as $property){
				foreach($instance->getPropertyValues($property) as $propertyValue){
					$returnValue->setPropertyValue($property, $propertyValue);
				}
			}
			$returnValue->setLabel($instance->getLabel()." bis");
		}
		
        // section 127-0-1-1-50de96c6:1266ae198e7:-8000:0000000000001E30 end

        return $returnValue;
    }

    /**
     * Format an RDFS Class to an array to be interpreted by the client tree
     * This is a closed array format.
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  boolean subclasses
     * @param  boolean instances
     * @param  string highlightUri
     * @param  string labelFilter
     * @return array
     */
    public function toTree( core_kernel_classes_Class $clazz, $subclasses = true, $instances = true, $highlightUri = '', $labelFilter = '')
    {
        $returnValue = array();

        // section 127-0-1-1-404a280c:12475f095ee:-8000:0000000000001A9B begin
		
		$instancesData = array();
		if($instances){
			foreach($clazz->getInstances(false) as $instance){
				$instanceData = array(
						'data' 	=> tao_helpers_Display::textCutter($instance->getLabel(), 16),
						'attributes' => array(
							'id' => tao_helpers_Uri::encode($instance->uriResource),
							'class' => 'node-instance'
						)
					);
				if(strlen($labelFilter) > 0){
					if(preg_match("/^".str_replace('*', '(.*)', $labelFilter."$/"), $instance->getLabel())){
						$instancesData[] = $instanceData;
					}
				}
				else{
					$instancesData[] = $instanceData;
				}
				
			}
		}
		$subclassesData = array();
		if($subclasses){
			foreach($clazz->getSubClasses(false) as $subclass){
				$subclassesData[] = $this->toTree($subclass, $subclasses, $instances, $highlightUri, $labelFilter);
			}
		}
		
		//format classes for json tree datastore 
		$data = array(
				'data' 	=> tao_helpers_Display::textCutter($clazz->getLabel(), 16),
				'attributes' => array(
						'id' => tao_helpers_Uri::encode($clazz->uriResource),
						'class' => 'node-class'
					)
 			);
		$children = array_merge($subclassesData, $instancesData);
		if(count($children) > 0){
			$data['children'] = $children;
		}
		if($highlightUri != ''){
			if($highlightUri == tao_helpers_Uri::encode($clazz->uriResource)){
				if(count($clazz->getInstances()) > 0 || count($clazz->getSubClasses()) > 0){
					$data['state'] = 'open';
				} 
			}
			else{
				foreach($clazz->getInstances() as $childInstance){
					if($highlightUri == tao_helpers_Uri::encode($childInstance->uriResource)){
						$data['state'] = 'open';
						break;
					}
				}
				$clazzChildren = $clazz->getSubClasses(true);
				foreach($clazzChildren as $clazzChild){
					if($highlightUri == tao_helpers_Uri::encode($clazzChild->uriResource)){
						$data['state'] = 'open';
						break;
					}
					foreach($clazzChild->getInstances() as $childInstance){
						if($highlightUri == tao_helpers_Uri::encode($childInstance->uriResource)){
							$data['state'] = 'open';
							break;
						}
					}
				}
			}
		}
		$returnValue = $data;
		
        // section 127-0-1-1-404a280c:12475f095ee:-8000:0000000000001A9B end

        return (array) $returnValue;
    }

    /**
     * Format an RDFS Class to an array
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @return array
     */
    public function toArray( core_kernel_classes_Class $clazz)
    {
        $returnValue = array();

        // section 127-0-1-1-1f98225a:12544a8e3a3:-8000:0000000000001C80 begin
		$properties = $clazz->getProperties(false); 
		foreach($clazz->getInstances(false) as $instance){
			$data = array();
			foreach($properties	as $property){
				
				$data[$property->getLabel()] = null;
				
				$values = $instance->getPropertyValues($property);
				if(count($values) > 1){
					$data[$property->getLabel()] = $values;
				}
				elseif(count($values) == 1){
					$data[$property->getLabel()] = $values[0];
				}
			}
			array_push($returnValue, $data);
		}
		
        // section 127-0-1-1-1f98225a:12544a8e3a3:-8000:0000000000001C80 end

        return (array) $returnValue;
    }

    /**
     * get the properties of an instance for a specific language
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource instance
     * @param  string lang
     * @return array
     */
    public function getTranslatedProperties( core_kernel_classes_Resource $instance, $lang)
    {
        $returnValue = array();

        // section 127-0-1-1--1254e308:126aced7510:-8000:0000000000001E84 begin
		
		try{
			$type = $instance->getUniquePropertyValue(new core_kernel_classes_Property(RDFS_TYPE));
			$clazz = new core_kernel_classes_Class($type->uriResource);
			
			foreach($clazz->getProperties(true) as $property){
				
				if($property->isLgDependent() || $property->uriResource == RDFS_LABEL){
					$collection = $instance->getPropertyValuesByLg($property, $lang);
					if($collection->count() > 0 ){
						
						if($collection->count() == 1){
							$returnValue[$property->uriResource] = (string)$collection->get(0);
						}
						else{
							$propData = array();
							foreach($collection->getIterator() as $collectionItem){
								$propData[] = (string)$collectionItem;
							}
							$returnValue[$property->uriResource] = $propData;
						}
					}
					
				}
			}
		}
		catch(Exception $e){
			print $e;
		}
		
        // section 127-0-1-1--1254e308:126aced7510:-8000:0000000000001E84 end

        return (array) $returnValue;
    }

    /**
     * set the properties of an instance for a specific language
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource instance
     * @param  string lang
     * @param  array data
     * @return boolean
     */
    public function setTranslatedProperties( core_kernel_classes_Resource $instance, $lang, $data)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--1254e308:126aced7510:-8000:0000000000001E88 begin
        // section 127-0-1-1--1254e308:126aced7510:-8000:0000000000001E88 end

        return (bool) $returnValue;
    }

} /* end of abstract class tao_models_classes_Service */

?>