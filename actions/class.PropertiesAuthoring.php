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
 * Copyright (c) 2015-2018 Open Assessment Technologies S.A.
 */

use oat\oatbox\event\EventManager;
use oat\tao\model\event\ClassFormUpdatedEvent;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\WidgetRdf;
use oat\tao\model\search\index\OntologyIndex;
use oat\tao\model\search\index\OntologyIndexService;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\log\LoggerAwareTrait;

/**
 * Regrouping all actions related to authoring
 * of properties
 */
class tao_actions_PropertiesAuthoring extends tao_actions_CommonModule
{
    use OntologyAwareTrait;
    use LoggerAwareTrait;

    /**
     * @return EventManager
     */
    protected function getEventManager()
    {
        return $this->getServiceLocator()->get(EventManager::SERVICE_ID);
    }

    /**
     * @requiresRight id READ
     */
    public function index()
    {
        $this->defaultData();
        $clazz = $this->getClass($this->getRequestParameter('id'));

        $myForm = $this->getClassForm($clazz);
        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {
                if ($clazz instanceof core_kernel_classes_Resource) {
                    $this->setData("selectNode", tao_helpers_Uri::encode($clazz->getUri()));
                    $properties = $this->hasRequestParameter('properties') ? $this->getRequestParameter('properties') : [];
                    $this->getEventManager()->trigger(new ClassFormUpdatedEvent($clazz, $properties));
                }
                $this->setData('message', __('%s Class saved', $clazz->getLabel()));
                $this->setData('reload', false);
            }
        }
        $this->setData('formTitle', __('Manage class schema'));
        $this->setData('myForm', $myForm->render());
        $this->setView('form.tpl', 'tao');
    }

    /**
     * Render the add property sub form.
     * @throws Exception
     * @throws common_exception_BadRequest
     * @return void
     * @requiresRight id WRITE
     */
    public function addClassProperty()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $clazz = $this->getClass($this->getRequestParameter('id'));

        if ($this->hasRequestParameter('index')) {
            $index = intval($this->getRequestParameter('index'));
        } else {
            $index = count($clazz->getProperties(false)) + 1;
        }

        $propFormContainer = new tao_actions_form_SimpleProperty($clazz, $clazz->createProperty('Property_' . $index), ['index' => $index]);
        $myForm = $propFormContainer->getForm();

        $this->setData('data', $myForm->renderElements());
        $this->setView('blank.tpl', 'tao');
    }


    /**
     * Render the add property sub form.
     * @throws Exception
     * @throws common_exception_BadRequest
     * @return void
     * @requiresRight classUri WRITE
     */
    public function removeClassProperty()
    {
        $success = false;
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }

        $class = $this->getClass($this->getRequestParameter('classUri'));
        $property = $this->getProperty($this->getRequestParameter('uri'));

        //delete property mode
        foreach ($class->getProperties() as $classProperty) {
            if ($classProperty->equals($property)) {
                $indexes = $property->getPropertyValues($this->getProperty(OntologyIndex::PROPERTY_INDEX));
                //delete property and the existing values of this property
                if ($property->delete(true)) {
                    //delete index linked to the property
                    foreach ($indexes as $indexUri) {
                        $index = $this->getResource($indexUri);
                        $index->delete(true);
                    }
                    $success = true;
                    break;
                }
            }
        }

        if ($success) {
            $this->returnJson([
                'success' => true
            ]);
            return;
        } else {
            $this->returnError(__('Unable to remove the property.'));
        }
    }

    /**
     * remove the index of the property.
     * @throws Exception
     * @throws common_exception_BadRequest
     * @return void
     */
    public function removePropertyIndex()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }
        if (!$this->hasRequestParameter('uri')) {
            throw new common_exception_MissingParameter("Uri parameter is missing");
        }

        if (!$this->hasRequestParameter('indexProperty')) {
            throw new common_exception_MissingParameter("indexProperty parameter is missing");
        }

        $indexPropertyUri = tao_helpers_Uri::decode($this->getRequestParameter('indexProperty'));

        //remove use of index property in property
        $property = $this->getProperty(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        $property->removePropertyValue($this->getProperty(OntologyIndex::PROPERTY_INDEX), $indexPropertyUri);

        //remove index property
        $indexProperty = new OntologyIndex($indexPropertyUri);
        $indexProperty->delete();

        $this->returnJson(['id' => $this->getRequestParameter('indexProperty')]);
    }

    /**
     * Render the add index sub form.
     * @throws Exception
     * @throws common_exception_BadRequest
     * @return void
     */
    public function addPropertyIndex()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }
        if (!$this->hasRequestParameter('uri')) {
            throw new Exception("wrong request Parameter");
        }
        $uri = $this->getRequestParameter('uri');

        $clazz = $this->getCurrentClass();

        $index = 1;
        if ($this->hasRequestParameter('index')) {
            $index = $this->getRequestParameter('index');
        }

        $propertyIndex = 1;
        if ($this->hasRequestParameter('propertyIndex')) {
            $propertyIndex = $this->getRequestParameter('propertyIndex');
        }

        //create and attach the new index property to the property
        $property = $this->getProperty(tao_helpers_Uri::decode($uri));
        $class = $this->getClass("http://www.tao.lu/Ontologies/TAO.rdf#Index");

        //get property range to select a default tokenizer
        /** @var core_kernel_classes_Class $range */
        $range = $property->getRange();
        //range is empty select item content
        $tokenizer = null;
        if (is_null($range)) {
            $tokenizer = $this->getResource('http://www.tao.lu/Ontologies/TAO.rdf#RawValueTokenizer');
        } else {
            $tokenizer = $range->getUri() === OntologyRdfs::RDFS_LITERAL
                ? $this->getResource('http://www.tao.lu/Ontologies/TAO.rdf#RawValueTokenizer')
                : $this->getResource('http://www.tao.lu/Ontologies/TAO.rdf#LabelTokenizer');
        }

        $indexClass = $this->getClass('http://www.tao.lu/Ontologies/TAO.rdf#Index');
        $i = 0;
        $indexIdentifierBackup = preg_replace('/[^a-z_0-9]/', '_', strtolower($property->getLabel()));
        $indexIdentifierBackup = ltrim(trim($indexIdentifierBackup, '_'), '0..9');
        $indexIdentifier = $indexIdentifierBackup;
        do {
            if ($i !== 0) {
                $indexIdentifier = $indexIdentifierBackup . '_' . $i;
            }
            $resources = $indexClass->searchInstances([OntologyIndex::PROPERTY_INDEX_IDENTIFIER => $indexIdentifier], ['like' => false]);
            $count = count($resources);
            $i++;
        } while ($count !== 0);

        $indexProperty = $class->createInstanceWithProperties([
                OntologyRdfs::RDFS_LABEL => preg_replace('/_/', ' ', ucfirst($indexIdentifier)),
                OntologyIndex::PROPERTY_INDEX_IDENTIFIER => $indexIdentifier,
                OntologyIndex::PROPERTY_INDEX_TOKENIZER => $tokenizer,
                OntologyIndex::PROPERTY_INDEX_FUZZY_MATCHING => GenerisRdf::GENERIS_TRUE,
                OntologyIndex::PROPERTY_DEFAULT_SEARCH  => GenerisRdf::GENERIS_FALSE,
            ]);

        $property->setPropertyValue($this->getProperty(OntologyIndex::PROPERTY_INDEX), $indexProperty);

        //generate form
        $indexFormContainer = new tao_actions_form_IndexProperty(new OntologyIndex($indexProperty), $propertyIndex . $index);
        $myForm = $indexFormContainer->getForm();
        $form = trim(preg_replace('/\s+/', ' ', $myForm->renderElements()));
        $this->returnJson(['form' => $form]);
    }

    protected function getCurrentClass()
    {
        $classUri = tao_helpers_Uri::decode($this->getRequestParameter('classUri'));
        if (is_null($classUri) || empty($classUri)) {
            $clazz = null;
            $resource = $this->getCurrentInstance();
            foreach ($resource->getTypes() as $type) {
                $clazz = $type;
                break;
            }
            if (is_null($clazz)) {
                throw new Exception("No valid class uri found");
            }
            $returnValue = $clazz;
        } else {
            $returnValue = $this->getClass($classUri);
        }

        return $returnValue;
    }

    protected function getCurrentInstance()
    {
        $uri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        if (is_null($uri) || empty($uri)) {
            throw new tao_models_classes_MissingRequestParameterException("uri");
        }
        return $this->getResource($uri);
    }

    /**
     * Create an edit form for a class and its property
     * and handle the submited data on save
     *
     * @param core_kernel_classes_Class    $clazz
     * @param core_kernel_classes_Resource $resource
     * @return tao_helpers_form_Form the generated form
     */
    public function getClassForm(core_kernel_classes_Class $clazz)
    {
        $data = $this->getRequestParameters();
        $formContainer = new tao_actions_form_Clazz($clazz, $this->extractClassData($data), $this->extractPropertyData($data));
        $myForm = $formContainer->getForm();

        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {
                //get the data from parameters

                // get class data and save them
                if (isset($data['class'])) {
                    $classValues = [];
                    foreach ($data['class'] as $key => $value) {
                        $classKey =  tao_helpers_Uri::decode($key);
                        $classValues[$classKey] =  tao_helpers_Uri::decode($value);
                    }

                    $this->bindProperties($clazz, $classValues);
                }

                //save all properties values
                if (isset($data['properties'])) {
                    foreach ($data['properties'] as $i => $propertyValues) {
                        //get index values
                        $indexes = null;
                        if (isset($propertyValues['indexes'])) {
                            $indexes = $propertyValues['indexes'];
                            unset($propertyValues['indexes']);
                        }
                        $this->saveSimpleProperty($propertyValues);
                        //save index
                        if (!is_null($indexes)) {
                            foreach ($indexes as $indexValues) {
                                $this->savePropertyIndex($indexValues);
                            }
                        }
                    }
                }
            }
        }
        return $myForm;
    }

    /**
     * Default property handling
     *
     * @param array $propertyValues
     */
    protected function saveSimpleProperty($propertyValues)
    {
        $propertyMap = tao_helpers_form_GenerisFormFactory::getPropertyMap();
        $property = $this->getProperty(tao_helpers_Uri::decode($propertyValues['uri']));
        $type = $propertyValues['type'];
        $range = (isset($propertyValues['range']) ? tao_helpers_Uri::decode(trim($propertyValues['range'])) : null);
        unset($propertyValues['uri']);
        unset($propertyValues['type']);
        unset($propertyValues['range']);
        $rangeNotEmpty = false;
        $values = [
            ValidationRuleRegistry::PROPERTY_VALIDATION_RULE => []
        ];

        if (isset($propertyMap[$type])) {
            $values[WidgetRdf::PROPERTY_WIDGET] = $propertyMap[$type]['widget'];
            $rangeNotEmpty = ($propertyMap[$type]['range'] === OntologyRdfs::RDFS_RESOURCE  );
        }

        foreach ($propertyValues as $key => $value) {
            if (is_string($value)) {
                $values[tao_helpers_Uri::decode($key)] = tao_helpers_Uri::decode($value);
            } elseif (is_array($value)) {
                $values[tao_helpers_Uri::decode($key)] = $value;
            } else {
                $this->logWarning('Unsuported value type ' . gettype($value));
            }
        }

        $rangeValidator = new tao_helpers_form_validators_NotEmpty(['message' => __('Range field is required')]);
        if ($rangeNotEmpty && !$rangeValidator->evaluate($range)) {
            throw new Exception($rangeValidator->getMessage());
        }

        $this->bindProperties($property, $values);

        // set the range
        $property->removePropertyValues($this->getProperty(OntologyRdfs::RDFS_RANGE));
        if (!empty($range)) {
            $property->setRange($this->getClass($range));
        } elseif (isset($propertyMap[$type]) && !empty($propertyMap[$type]['range'])) {
            $property->setRange($this->getClass($propertyMap[$type]['range']));
        }

        // set cardinality
        if (isset($propertyMap[$type]['multiple'])) {
            $property->setMultiple($propertyMap[$type]['multiple'] == GenerisRdf::GENERIS_TRUE);
        }
    }

    protected function savePropertyIndex($indexValues)
    {
        $values = [];
        foreach ($indexValues as $key => $value) {
            $values[tao_helpers_Uri::decode($key)] = tao_helpers_Uri::decode($value);
        }

        $validator = new tao_helpers_form_validators_IndexIdentifier();

        // if the identifier is valid
        $values[OntologyIndex::PROPERTY_INDEX_IDENTIFIER] = strtolower($values[OntologyIndex::PROPERTY_INDEX_IDENTIFIER]);
        if (!$validator->evaluate($values[OntologyIndex::PROPERTY_INDEX_IDENTIFIER])) {
            throw new Exception($validator->getMessage());
        }

        //if the property exists edit it, else create one
        $existingIndex = OntologyIndexService::getIndexById($values[OntologyIndex::PROPERTY_INDEX_IDENTIFIER]);
        $indexProperty = $this->getProperty($values['uri']);
        if (!is_null($existingIndex) && !$existingIndex->equals($indexProperty)) {
            throw new Exception("The index identifier should be unique");
        }
        unset($values['uri']);
        $this->bindProperties($indexProperty, $values);
    }

    /**
     * Helper to save class and properties
     *
     * @param core_kernel_classes_Resource $resource
     * @param array $values
     */
    protected function bindProperties(core_kernel_classes_Resource $resource, $values)
    {
        $binder = new tao_models_classes_dataBinding_GenerisInstanceDataBinder($resource);
        $binder->bind($values);
    }

    /**
     * Extracts the data assoicuated with the class from the request
     *
     * @param array $data
     * @return array
     */
    protected function extractClassData($data)
    {
        $classData = [];
        if (isset($data['class'])) {
            foreach ($data['class'] as $key => $value) {
                $classData['class_' . $key] = $value;
            }
        }
        return $classData;
    }

    /**
     * Extracts the properties data from the request data, and formats
     * it as an array with the keys being the property URI and the values
     * being the associated data
     *
     * @param array $data
     * @return array
     */
    protected function extractPropertyData($data)
    {
        $propertyData = [];
        if (isset($data['properties'])) {
            foreach ($data['properties'] as $key => $value) {
                $propertyData[tao_helpers_Uri::decode($value['uri'])] = $value;
            }
        }
        return $propertyData;
    }
}
