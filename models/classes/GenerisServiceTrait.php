<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2018-2021 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\generis\model\fileReference\ResourceFileSerializer;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\session\SessionService;
use oat\tao\helpers\TreeHelper;
use oat\tao\model\event\ClassMovedEvent;

/**
 * Trait GenerisServiceTrait
 * @package oat\tao\model
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
trait GenerisServiceTrait
{
    use EventManagerAwareTrait;
    use OntologyAwareTrait;

    /**
     * search the instances matching the filters in parameters
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array propertyFilters
     * @param  core_kernel_classes_Class $topClazz
     * @param  array options
     * @return \core_kernel_classes_Resource[]
     */
    public function searchInstances($propertyFilters = [], core_kernel_classes_Class $topClazz = null, $options = [])
    {
        $returnValue = [];

        if (!is_null($topClazz)) {
            $returnValue = $topClazz->searchInstances($propertyFilters, $options);
        }
        return (array) $returnValue;
    }

    /**
     * Instantiate an RDFs Class
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Class $clazz
     * @param  string label
     * @return core_kernel_classes_Resource
     * @throws \common_exception_Error
     */
    public function createInstance(core_kernel_classes_Class $clazz, $label = '')
    {
        if (empty($label)) {
            $label = $this->createUniqueLabel($clazz);
        }
        return $clazz->createInstance($label);
    }

    /**
     * Short description of method createUniqueLabel
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Class $clazz
     * @param  boolean $subClassing
     * @throws \common_exception_Error
     * @return string
     */
    public function createUniqueLabel(core_kernel_classes_Class $clazz, $subClassing = false)
    {
        if ($subClassing) {
            $labelBase = $clazz->getLabel() . '_' ;
            $count = count($clazz->getSubClasses()) + 1;
        } else {
            $labelBase = $clazz->getLabel() . ' ' ;
            $count = count($clazz->getInstances()) + 1;
        }

        $options = [
            'lang' => $this
                ->getServiceLocator()
                ->get(SessionService::SERVICE_ID)
                ->getCurrentSession()
                ->getDataLanguage(),
            'like' => false,
            'recursive' => false
        ];

        do {
            $exist = false;
            $label =  $labelBase . $count;
            $result = $clazz->searchInstances([OntologyRdfs::RDFS_LABEL => $label], $options);
            if (count($result) > 0) {
                $exist = true;
                $count++;
            }
        } while ($exist);

        return (string) $label;
    }

    /**
     * Subclass an RDFS Class
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Class $parentClazz
     * @param  string $label
     * @return core_kernel_classes_Class
     * @throws \common_exception_Error
     */
    public function createSubClass(core_kernel_classes_Class $parentClazz, $label = '')
    {
        if (empty($label)) {
            $label = $this->createUniqueLabel($parentClazz, true);
        }
        return $parentClazz->createSubClass($label, '');
    }

    /**
     * bind the given RDFS properties to the RDFS resource in parameter
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Resource $instance
     * @param  array properties
     * @return core_kernel_classes_Resource
     * @throws \tao_models_classes_dataBinding_GenerisInstanceDataBindingException
     */
    public function bindProperties(core_kernel_classes_Resource $instance, $properties = [])
    {
        $binder = new \tao_models_classes_dataBinding_GenerisInstanceDataBinder($instance);
        $binder->bind($properties);

        return $instance;
    }

    /**
     * @deprecated Use `oat\tao\model\resources\Service\InstanceCopier::transfer()` instead for Items/Tests/Assets
     *
     * duplicate a resource
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Resource $instance
     * @param  core_kernel_classes_Class $clazz
     * @return core_kernel_classes_Resource
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    public function cloneInstance(core_kernel_classes_Resource $instance, core_kernel_classes_Class $clazz = null)
    {
        if (is_null($clazz)) {
            $types = $instance->getTypes();
            $clazz = current($types);
        }

        $returnValue = $this->createInstance($clazz);
        if (!is_null($returnValue)) {
            $properties = $clazz->getProperties(true);
            foreach ($properties as $property) {
                $this->cloneInstanceProperty($instance, $returnValue, $property);
            }
            $label = $instance->getLabel();
            $cloneLabel = "$label bis";
            if (preg_match("/bis(\s[0-9]+)?$/", $label)) {
                $cloneNumber = (int)preg_replace("/^(.?)*bis/", "", $label);
                $cloneNumber++;
                $cloneLabel = preg_replace("/bis(\s[0-9]+)?$/", "", $label) . "bis $cloneNumber" ;
            }

            $returnValue->setLabel($cloneLabel);
        }

        return $returnValue;
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @param core_kernel_classes_Resource $source
     * @param core_kernel_classes_Resource $destination
     * @param core_kernel_classes_Property $property
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    protected function cloneInstanceProperty(
        core_kernel_classes_Resource $source,
        core_kernel_classes_Resource $destination,
        core_kernel_classes_Property $property
    ) {
        $range = $property->getRange();
        // Avoid doublons, the RDF TYPE property will be set by the implementation layer
        if ($property->getUri() != OntologyRdf::RDF_TYPE) {
            foreach ($source->getPropertyValuesCollection($property)->getIterator() as $propertyValue) {
                if (!is_null($range) && $range->getUri() == GenerisRdf::CLASS_GENERIS_FILE) {
                    /** @var FileReferenceSerializer $fileRefSerializer */
                    $fileRefSerializer = $this->getServiceLocator()
                        ->get(ResourceFileSerializer::SERVICE_ID);

                    /** @var File $oldFile */
                    $oldFile = $fileRefSerializer->unserializeFile($propertyValue->getUri());

                    $newFileName = \helpers_File::createFileName($oldFile->getBasename());

                    /** @var File $newFile */
                    $newFile = $this->getServiceLocator()
                        ->get(FileSystemService::SERVICE_ID)
                        ->getDirectory($oldFile->getFileSystemId())
                        ->getFile($newFileName);

                    $newFile->write($oldFile->readStream());

                    $newFileUri = $fileRefSerializer->serialize($newFile);

                    $destination->setPropertyValue($property, new core_kernel_classes_Resource($newFileUri));
                } else {
                    $destination->setPropertyValue($property, $propertyValue);
                }
            }
        }
    }


    /**
     * Clone a Class and move it under the newParentClazz
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Class $sourceClazz
     * @param  core_kernel_classes_Class $newParentClazz
     * @param  core_kernel_classes_Class $topLevelClazz
     * @return core_kernel_classes_Class|null
     * @throws \common_exception_Error
     */
    public function cloneClazz(
        core_kernel_classes_Class $sourceClazz,
        core_kernel_classes_Class $newParentClazz = null,
        core_kernel_classes_Class $topLevelClazz = null
    ) {
        $returnValue = null;

        if (!is_null($sourceClazz) && !is_null($newParentClazz)) {
            if ((is_null($topLevelClazz))) {
                $properties = $sourceClazz->getProperties(false);
            } else {
                $properties = $this->getClazzProperties($sourceClazz, $topLevelClazz);
            }

            //check for duplicated properties
            $newParentProperties = $newParentClazz->getProperties(true);
            foreach ($properties as $index => $property) {
                foreach ($newParentProperties as $newParentProperty) {
                    if ($property->getUri() == $newParentProperty->getUri()) {
                        unset($properties[$index]);
                        break;
                    }
                }
            }

            //create a new class
            $returnValue = $this->createSubClass($newParentClazz, $sourceClazz->getLabel());

            //assign the properties of the source class
            foreach ($properties as $property) {
                $property->setDomain($returnValue);
            }
        }
        return $returnValue;
    }

    /**
     * Change the Class (RDF_TYPE) of a resource
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Resource $instance
     * @param  core_kernel_classes_Class $destinationClass
     * @return boolean
     */
    public function changeClass(core_kernel_classes_Resource $instance, core_kernel_classes_Class $destinationClass)
    {
        if ($instance->isClass()) {
            try {
                /** @var core_kernel_classes_Class $instance */
                $status = $instance->editPropertyValues(
                    $this->getProperty(OntologyRdfs::RDFS_SUBCLASSOF),
                    $destinationClass
                );
                if ($status) {
                    $this->getEventManager()->trigger(new ClassMovedEvent($instance));
                }
                return $status;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            try {
                foreach ($instance->getTypes() as $type) {
                    $instance->removeType($type);
                }
                $instance->setType($destinationClass);
                foreach ($instance->getTypes() as $type) {
                    if ($type->getUri() == $destinationClass->getUri()) {
                        return true;
                    }
                }
            } catch (\common_Exception $ce) {
                print $ce;
                return false;
            }
        }
    }

    /**
     * Get all the properties of the class in parameter.
     * The properties are taken recursively into the class parents up to the top
     * class.
     * If the top level class is not defined, we used the TAOObject class.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Class $clazz
     * @param  core_kernel_classes_Class $topLevelClazz
     * @return array
     */
    public function getClazzProperties(
        core_kernel_classes_Class $clazz,
        core_kernel_classes_Class $topLevelClazz = null
    ) {
        $returnValue = [];
        if (is_null($topLevelClazz)) {
            $topLevelClazz = new core_kernel_classes_Class(TaoOntology::CLASS_URI_OBJECT);
        }

        if ($clazz->getUri() == $topLevelClazz->getUri()) {
            $returnValue = $clazz->getProperties(false);
            return (array) $returnValue;
        }

        //determine the parent path
        $parents = [];
        $top = false;
        do {
            if (!isset($lastLevelParents)) {
                $parentClasses = $clazz->getParentClasses(false);
            } else {
                $parentClasses = [];
                foreach ($lastLevelParents as $parent) {
                    $parentClasses = array_merge($parentClasses, $parent->getParentClasses(false));
                }
            }
            if (count($parentClasses) == 0) {
                break;
            }
            $lastLevelParents = [];
            foreach ($parentClasses as $parentClass) {
                if ($parentClass->getUri() == $topLevelClazz->getUri()) {
                    $parents[$parentClass->getUri()] = $parentClass;
                    $top = true;
                    break;
                }
                if ($parentClass->getUri() == OntologyRdfs::RDFS_CLASS) {
                    continue;
                }

                $allParentClasses = $parentClass->getParentClasses(true);
                if (array_key_exists($topLevelClazz->getUri(), $allParentClasses)) {
                    $parents[$parentClass->getUri()] = $parentClass;
                }
                $lastLevelParents[$parentClass->getUri()] = $parentClass;
            }
        } while (!$top);

        foreach ($parents as $parent) {
            $returnValue = array_merge($returnValue, $parent->getProperties(false));
        }

        $returnValue = array_merge($returnValue, $clazz->getProperties(false));

        return (array) $returnValue;
    }

    /**
     * get the properties of the source class that are not in the destination
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param core_kernel_classes_Class $sourceClass
     * @param core_kernel_classes_Class $destinationClass
     * @return array
     */
    public function getPropertyDiff(core_kernel_classes_Class $sourceClass, core_kernel_classes_Class $destinationClass)
    {
        $returnValue = [];
        $sourceProperties = $sourceClass->getProperties(true);
        $destinationProperties = $destinationClass->getProperties(true);
        foreach ($sourceProperties as $sourcePropertyUri => $sourceProperty) {
            if (!array_key_exists($sourcePropertyUri, $destinationProperties)) {
                $sourceProperty->getLabel();
                array_push($returnValue, $sourceProperty);
            }
        }
        return (array) $returnValue;
    }

    /**
     * get the properties of an instance for a specific language
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Resource $instance
     * @param  string $lang
     * @return array
     */
    public function getTranslatedProperties(core_kernel_classes_Resource $instance, $lang)
    {
        $returnValue = [];

        try {
            foreach ($instance->getTypes() as $clazz) {
                foreach ($clazz->getProperties(true) as $property) {
                    if ($property->isLgDependent() || $property->getUri() == OntologyRdfs::RDFS_LABEL) {
                        $collection = $instance->getPropertyValuesByLg($property, $lang);
                        if ($collection->count() > 0) {
                            if ($collection->count() == 1) {
                                $returnValue[$property->getUri()] = (string)$collection->get(0);
                            } else {
                                $propData = [];
                                foreach ($collection->getIterator() as $collectionItem) {
                                    $propData[] = (string)$collectionItem;
                                }
                                $returnValue[$property->getUri()] = $propData;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            print $e;
        }

        return (array) $returnValue;
    }

    /**
     * Format an RDFS Class to an array
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param core_kernel_classes_Class $clazz
     * @return array
     */
    public function toArray(core_kernel_classes_Class $clazz)
    {
        $returnValue = [];
        $properties = $clazz->getProperties(false);
        foreach ($clazz->getInstances(false) as $instance) {
            $data = [];
            foreach ($properties as $property) {
                $data[$property->getLabel()] = null;
                $values = $instance->getPropertyValues($property);
                if (count($values) > 1) {
                    $data[$property->getLabel()] = $values;
                } elseif (count($values) == 1) {
                    $data[$property->getLabel()] = $values[0];
                }
            }
            array_push($returnValue, $data);
        }
        return (array) $returnValue;
    }

    /**
     * Format an RDFS Class to an array to be interpreted by the client tree
     * This is a closed array format.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  core_kernel_classes_Class $clazz
     * @param  array $options
     * @return array
     * @throws \common_exception_Error
     */
    public function toTree(core_kernel_classes_Class $clazz, array $options = [])
    {
        $searchOptions = [];
        // show instances yes/no
        $instances = (isset($options['instances'])) ? $options['instances'] : true;
        // cut of the class and only display the children?
        $chunk = (isset($options['chunk'])) ? $options['chunk'] : false;
        // probably which subtrees should be opened
        $browse = (isset($options['browse'])) ? $options['browse'] : [];
        // limit of instances shown by subclass if no search label is given
        // if a search string is given, this is the total limit of results, independent of classes
        $limit = (isset($options['limit'])) ? $options['limit'] : 0;
        // offset for limit
        $offset = (isset($options['offset'])) ? $options['offset'] : 0;
        // A unique node URI to be returned from as a tree leaf.
        $uniqueNode = (isset($options['uniqueNode'])) ? $options['uniqueNode'] : null;

        if (isset($options['order']) && isset($options['orderdir'])) {
            $searchOptions['order'] = [$options['order'] => $options['orderdir']];
        }

        if ($uniqueNode !== null) {
            $instance = new \core_kernel_classes_Resource($uniqueNode);
            $results[] = TreeHelper::buildResourceNode($instance, $clazz);
            $returnValue = $results;
        } else {
            // Let's walk the tree with super walker! ~~~ p==[w]õ__
            array_walk($browse, function (&$item) {
                $item = \tao_helpers_Uri::decode($item);
            });
            $openNodes = TreeHelper::getNodesToOpen($browse, $clazz);

            if (!in_array($clazz->getUri(), $openNodes)) {
                $openNodes[] = $clazz->getUri();
            }

            $factory = new GenerisTreeFactory(
                $instances,
                $openNodes,
                $limit,
                $offset,
                $browse,
                $this->getDefaultFilters(),
                $searchOptions
            );
            $tree = $factory->buildTree($clazz);
            $returnValue = $chunk
                ? (isset($tree['children']) ? $tree['children'] : [])
                : $tree;
        }
        return $returnValue;
    }

    /**
     * @return array
     */
    protected function getDefaultFilters()
    {
        return [];
    }

    /**
     * make sure that trait will be mixed to ServiceLocatorAware class
     * @return mixed
     */
    abstract public function getServiceLocator();
}
