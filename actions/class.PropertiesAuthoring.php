<?php

use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\model\event\ClassFormUpdatedEvent;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\WidgetRdf;
use oat\tao\model\search\Index;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\tao\model\TaoOntology;

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
 * Copyright (c) 2015 Open Assessment Technologies S.A.
 */

/**
 * Regrouping all actions related to authoring
 * of properties
 */
class tao_actions_PropertiesAuthoring extends tao_actions_CommonModule
{
    use EventManagerAwareTrait;

    /**
     * @requiresRight id READ
     */
    public function index()
    {
        $this->defaultData();
        $clazz = new core_kernel_classes_Class($this->getRequestParameter('id'));
         
        if ($this->hasRequestParameter('property_mode')) {
            $this->setSessionAttribute('property_mode', $this->getRequestParameter('property_mode'));
        }
         
        $myForm = $this->getClassForm($clazz);
        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {
                if ($clazz instanceof core_kernel_classes_Resource) {
                    $this->setData("selectNode", tao_helpers_Uri::encode($clazz->getUri()));
                    $properties = $this->hasRequestParameter('properties') ? $this->getRequestParameter('properties') : [];
                    $this->getEventManager()->trigger(new ClassFormUpdatedEvent($clazz, $properties));
                }
                $this->setData('message', __('%s Class saved', $clazz->getLabel()));
                $this->setData('reload', true);
            }
        }
        $this->setData('formTitle', __('Edit class %s', $clazz->getLabel()));
        $this->setData('myForm', $myForm->render());
        $this->setView('form.tpl', 'tao');
    }
    
    /**
     * Render the add property sub form.
     * @throws Exception
     * @return void
     * @requiresRight id WRITE
     */
    public function addClassProperty()
    {
        if(!tao_helpers_Request::isAjax()){
            throw new Exception("wrong request mode");
        }
    
        $clazz = new core_kernel_classes_Class($this->getRequestParameter('id'));
    
        if($this->hasRequestParameter('index')){
            $index = intval($this->getRequestParameter('index'));
        }
        else{
            $index = count($clazz->getProperties(false)) + 1;
        }
    
        $propMode = 'simple';
        if($this->hasSessionAttribute('property_mode')){
            $propMode = $this->getSessionAttribute('property_mode');
        }
    
        //instanciate a property form
        $propFormClass = 'tao_actions_form_'.ucfirst(strtolower($propMode)).'Property';
        if(!class_exists($propFormClass)){
            $propFormClass = 'tao_actions_form_SimpleProperty';
        }
    
        $propFormContainer = new $propFormClass($clazz, $clazz->createProperty('Property_'.$index), array('index' => $index));
        $myForm = $propFormContainer->getForm();
    
        $this->setData('data', $myForm->renderElements());
        $this->setView('blank.tpl', 'tao');
    }
    

    /**
     * Render the add property sub form.
     * @throws Exception
     * @return void
     * @requiresRight classUri WRITE
     */
    public function removeClassProperty()
    {
        $success = false;
        if(!tao_helpers_Request::isAjax()){
            throw new Exception("wrong request mode");
        }
        
        $class = new core_kernel_classes_Class($this->getRequestParameter('classUri'));
        $property = new core_kernel_classes_Property($this->getRequestParameter('uri'));
    
        //delete property mode
        foreach($class->getProperties() as $classProperty) {
            if ($classProperty->equals($property)) {

                $indexes = $property->getPropertyValues(new core_kernel_classes_Property(Index::PROPERTY_INDEX));
                //delete property and the existing values of this property
                if($property->delete(true)){
                    //delete index linked to the property
                    foreach($indexes as $indexUri){
                        $index = new core_kernel_classes_Resource($indexUri);
                        $index->delete(true);
                    }
                    $success = true;
                    break;
                }
            }
        }
        
        if ($success) {
            $this->returnJson(array(
                'success' => true
            ));
            return;
        } else {
            $this->returnError(__('Unable to remove the property.'));
        }
    }

    /**
     * remove the index of the property.
     * @throws Exception
     * @return void
     */
    public function removePropertyIndex()
    {
        if(!tao_helpers_Request::isAjax()){
            throw new Exception("wrong request mode");
        }
        if(!$this->hasRequestParameter('uri')){
            throw new common_exception_MissingParameter("Uri parameter is missing");
        }

        if(!$this->hasRequestParameter('indexProperty')){
            throw new common_exception_MissingParameter("indexProperty parameter is missing");
        }

        $indexPropertyUri = tao_helpers_Uri::decode($this->getRequestParameter('indexProperty'));

        //remove use of index property in property
        $property = new core_kernel_classes_Property(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        $property->removePropertyValue(new core_kernel_classes_Property(Index::PROPERTY_INDEX),$indexPropertyUri);

        //remove index property
        $indexProperty = new \oat\tao\model\search\Index($indexPropertyUri);
        $indexProperty->delete();

        echo json_encode(array('id' => $this->getRequestParameter('indexProperty')));
    }

    /**
     * Render the add index sub form.
     * @throws Exception
     * @return void
     */
    public function addPropertyIndex()
    {
        if(!tao_helpers_Request::isAjax()){
            throw new Exception("wrong request mode");
        }
        if(!$this->hasRequestParameter('uri')){
            throw new Exception("wrong request Parameter");
        }
        $uri = $this->getRequestParameter('uri');

        $clazz = $this->getCurrentClass();

        $index = 1;
        if($this->hasRequestParameter('index')){
            $index = $this->getRequestParameter('index');
        }

        $propertyIndex = 1;
        if($this->hasRequestParameter('propertyIndex')){
            $propertyIndex = $this->getRequestParameter('propertyIndex');
        }



        //create and attach the new index property to the property
        $property = new core_kernel_classes_Property(tao_helpers_Uri::decode($uri));
        $class = new \core_kernel_classes_Class("http://www.tao.lu/Ontologies/TAO.rdf#Index");

        //get property range to select a default tokenizer
        /** @var core_kernel_classes_Class $range */
        $range = $property->getRange();
        //range is empty select item content
        $tokenizer = null;
        if (is_null($range)) {
            $tokenizer = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAO.rdf#RawValueTokenizer');
        } else {
            $tokenizer = $range->getUri() === OntologyRdfs::RDFS_LITERAL
                ? new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAO.rdf#RawValueTokenizer')
                : new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAO.rdf#LabelTokenizer');
        }

        $indexClass = new core_kernel_classes_Class('http://www.tao.lu/Ontologies/TAO.rdf#Index');
        $i = 0;
        $indexIdentifierBackup = preg_replace('/[^a-z_0-9]/','_',strtolower($property->getLabel()));
        $indexIdentifierBackup = ltrim(trim($indexIdentifierBackup, '_'),'0..9');
        $indexIdentifier = $indexIdentifierBackup;
        do{
            if($i !== 0){
                $indexIdentifier = $indexIdentifierBackup.'_'.$i;
            }
            $resources = $indexClass->searchInstances(array(Index::PROPERTY_INDEX_IDENTIFIER => $indexIdentifier), array('like' => false));
            $count = count($resources);
            $i++;
        }while($count !== 0);

        $indexProperty = $class->createInstanceWithProperties(array(
                OntologyRdfs::RDFS_LABEL => preg_replace('/_/',' ',ucfirst($indexIdentifier)),
                Index::PROPERTY_INDEX_IDENTIFIER => $indexIdentifier,
                Index::PROPERTY_INDEX_TOKENIZER => $tokenizer,
                Index::PROPERTY_INDEX_FUZZY_MATCHING => GenerisRdf::GENERIS_TRUE,
                Index::PROPERTY_DEFAULT_SEARCH  => GenerisRdf::GENERIS_FALSE,
            ));

        $property->setPropertyValue(new core_kernel_classes_Property(Index::PROPERTY_INDEX), $indexProperty);

        //generate form
        $indexFormContainer = new tao_actions_form_IndexProperty(new Index($indexProperty), $propertyIndex.$index);
        $myForm = $indexFormContainer->getForm();
        $form = trim(preg_replace('/\s+/', ' ', $myForm->renderElements()));
        echo json_encode(array('form' => $form));
    }

    protected function getCurrentClass()
    {
        $classUri = tao_helpers_Uri::decode($this->getRequestParameter('classUri'));
        if(is_null($classUri) || empty($classUri)){

            $clazz = null;
            $resource = $this->getCurrentInstance();
            foreach($resource->getTypes() as $type){
                $clazz = $type;
                break;
            }
            if(is_null($clazz)){
                throw new Exception("No valid class uri found");
            }
            $returnValue = $clazz;
        }
        else{
            $returnValue = new core_kernel_classes_Class($classUri);
        }

        return $returnValue;
    }

    protected function getCurrentInstance()
    {
        $uri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        if(is_null($uri) || empty($uri)){
            throw new tao_models_classes_MissingRequestParameterException("uri");
        }
        return new core_kernel_classes_Resource($uri);
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
    
        $propMode = 'simple';
        if($this->hasSessionAttribute('property_mode')){
            $propMode = $this->getSessionAttribute('property_mode');
        }
    
        $options = array(
            'property_mode' => $propMode,
            'topClazz' => new core_kernel_classes_Class(GenerisRdf::CLASS_GENERIS_RESOURCE)
        );
        $data = $this->getRequestParameters();
        $formContainer = new tao_actions_form_Clazz($clazz, $this->extractClassData($data), $this->extractPropertyData($data), $propMode);
        $myForm = $formContainer->getForm();
    
        if($myForm->isSubmited()){
            if($myForm->isValid()){
                //get the data from parameters
    
                // get class data and save them
                if(isset($data['class'])){
                    $classValues = array();
                    foreach($data['class'] as $key => $value){
                        $classKey =  tao_helpers_Uri::decode($key);
                        $classValues[$classKey] =  tao_helpers_Uri::decode($value);
                    }
                    
                    $this->bindProperties($clazz, $classValues);
                }
    
                //save all properties values
                if(isset($data['properties'])){
                    foreach($data['properties'] as $i => $propertyValues) {
                        //get index values
                        $indexes = null;
                        if(isset($propertyValues['indexes'])){
                            $indexes = $propertyValues['indexes'];
                            unset($propertyValues['indexes']);
                        }
                        if($propMode === 'simple') {
                            $this->saveSimpleProperty($propertyValues);
                        } else {
                            $this->saveAdvProperty($propertyValues);
                        }

                        //save index
                        if(!is_null($indexes)){
                            foreach($indexes as $indexValues){
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
        $property = new core_kernel_classes_Property(tao_helpers_Uri::decode($propertyValues['uri']));
        $type = $propertyValues['type'];
        $range = (isset($propertyValues['range']) ? tao_helpers_Uri::decode(trim($propertyValues['range'])) : null);
        unset($propertyValues['uri']);
        unset($propertyValues['type']);
        unset($propertyValues['range']);
        $rangeNotEmpty = false;
        $values = array(
            ValidationRuleRegistry::PROPERTY_VALIDATION_RULE => array()
        );

        if (isset($propertyMap[$type])) {
            $values[WidgetRdf::PROPERTY_WIDGET] = $propertyMap[$type]['widget'];
            $rangeNotEmpty = ($propertyMap[$type]['range'] === OntologyRdfs::RDFS_RESOURCE  );
        }
        
        foreach($propertyValues as $key => $value){
            if (is_string($value)) {
                $values[tao_helpers_Uri::decode($key)] = tao_helpers_Uri::decode($value);
            } elseif (is_array($value)) {
                $values[tao_helpers_Uri::decode($key)] = $value;
            } else {
                common_Logger::w('Unsuported value type '.gettype($value));
            }
        }

        $rangeValidator = new tao_helpers_form_validators_NotEmpty(array('message' => __('Range field is required')));
        if($rangeNotEmpty && !$rangeValidator->evaluate($range)){
            throw new Exception($rangeValidator->getMessage());
        }

        $this->bindProperties($property, $values);

        // set the range
        $property->removePropertyValues(new core_kernel_classes_Property(OntologyRdfs::RDFS_RANGE));
        if(!empty($range)) {
            $property->setRange(new core_kernel_classes_Class($range));
        } elseif (isset($propertyMap[$type]) && !empty($propertyMap[$type]['range'])) {
            $property->setRange(new core_kernel_classes_Class($propertyMap[$type]['range']));
        }
        
        // set cardinality
        if(isset($propertyMap[$type]['multiple'])) {
            $property->setMultiple($propertyMap[$type]['multiple'] == GenerisRdf::GENERIS_TRUE);
        }
    }
    
    /**
     * Advanced property handling
     *
     * @param array $propertyValues
     */
    protected function saveAdvProperty($propertyValues)
    {
        // might break using hard
        $range = array();
        foreach($propertyValues as $key => $value){
            if(is_array($value)){
                // set the range
                foreach($value as $v){
                    $range[] = new core_kernel_classes_Class(tao_helpers_Uri::decode($v));
                }
            }
            else{
                $values[tao_helpers_Uri::decode($key)] = tao_helpers_Uri::decode($value);
            }
        
        }
        //if label is empty
        $validator = new tao_helpers_form_validators_NotEmpty(array('message' => __('Property\'s label field is required')));
        if(!$validator->evaluate($values[OntologyRdfs::RDFS_LABEL])){
            throw new Exception($validator->getMessage());
        }

        $property = new core_kernel_classes_Property($values['uri']);
        unset($values['uri']);
        $property->removePropertyValues(new core_kernel_classes_Property(OntologyRdfs::RDFS_RANGE));
        if(!empty($range)){
            foreach($range as $r){
                $property->setRange($r);
            }
        }
        $this->bindProperties($property, $values);
    }

    protected function savePropertyIndex($indexValues)
    {
        $values = array();
        foreach($indexValues as $key => $value){
            $values[tao_helpers_Uri::decode($key)] = tao_helpers_Uri::decode($value);
        }

        $validator = new tao_helpers_form_validators_IndexIdentifier();

        // if the identifier is valid
        $values[Index::PROPERTY_INDEX_IDENTIFIER] = strtolower($values[Index::PROPERTY_INDEX_IDENTIFIER]);
        if(!$validator->evaluate($values[Index::PROPERTY_INDEX_IDENTIFIER])){
            throw new Exception($validator->getMessage());
        }

        //if the property exists edit it, else create one
        $existingIndex = \oat\tao\model\search\IndexService::getIndexById($values[Index::PROPERTY_INDEX_IDENTIFIER]);
        $indexProperty = new core_kernel_classes_Property($values['uri']);
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
    protected function bindProperties(core_kernel_classes_Resource $resource, $values) {
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
        $classData = array();
        if (isset($data['class'])) {
            foreach ($data['class'] as $key => $value) {
                $classData['class_'.$key] = $value;
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
        $propertyData = array();
        if (isset($data['properties'])) {
            foreach ($data['properties'] as $key => $value) {
                $propertyData[tao_helpers_Uri::decode($value['uri'])] = $value;
            }
        }
        return $propertyData;
    }
}
