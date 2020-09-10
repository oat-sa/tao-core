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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2017      (update and modification) Open Assessment Technologies SA ;
 *
 */

use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\form\ElementMapFactory;
use oat\tao\helpers\form\elements\ElementValue;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\TaoOntology;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create a form from a  resource of your ontology.
 * Each property will be a field, regarding it's widget.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao

 */
class tao_actions_form_Instance extends tao_actions_form_Generis
{
    /**
     * Initialize the form
     *
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     *
     * @throws common_Exception
     */
    protected function initForm()
    {
        $name = isset($this->options['name']) ? $this->options['name'] : '';
        if (empty($name)) {
            $name = 'form_' . (count(self::$forms) + 1);
        }
        unset($this->options['name']);

        $this->form = tao_helpers_form_FormFactory::getForm($name, $this->options);

        //add translate action in toolbar
        $actions = tao_helpers_form_FormFactory::getCommonActions();

        //add a hidden form element that states that it is an Instance Form.
        $instanceElt = tao_helpers_form_FormFactory::getElement('tao.forms.instance', 'Hidden');
        $instanceElt->setValue('1');
        $this->form->addElement($instanceElt, true);

        $this->form->setActions($actions, 'top');
        $this->form->setActions($actions, 'bottom');
    }

    /**
     * Initialize the form elements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     */
    protected function initElements()
    {
        $clazz = $this->getClazz();
        $instance = $this->getInstance();
        $guiOrderProperty = new core_kernel_classes_Property(TaoOntology::PROPERTY_GUI_ORDER);

        //get the list of properties to set in the form
        $propertyCandidates = tao_helpers_form_GenerisFormFactory::getDefaultProperties();

        $classProperties = tao_helpers_form_GenerisFormFactory::getClassProperties($clazz, $this->getTopClazz());
        $propertyCandidates = array_merge($propertyCandidates, $classProperties);

        $additionalProperties = (isset($this->options['additionalProperties']) && is_array($this->options['additionalProperties'])) ? $this->options['additionalProperties'] : [];
        if (!empty($additionalProperties)) {
            $propertyCandidates = array_merge($propertyCandidates, $additionalProperties);
        }

        $excludedProperties = (isset($this->options['excludedProperties']) && is_array($this->options['excludedProperties'])) ? $this->options['excludedProperties'] : [];
        $editedProperties = [];
        foreach ($propertyCandidates as $property) {
            if (!isset($editedProperties[$property->getUri()]) && !in_array($property->getUri(), $excludedProperties)) {
                $editedProperties[$property->getUri()] = $property;
            }
        }

        $finalElements = [];
        foreach ($editedProperties as $property) {
            $property->feed();
            $widget = $property->getWidget();
            if ($widget === null || $widget instanceof core_kernel_classes_Literal) {
                continue;
            }

            //map properties widgets to form elments

            $element = $this->getElementFactory()->create($property);

            if ($element !== null) {
                // take instance values to populate the form
                if ($instance !== null) {
                    $isList = $this->isList($property);
                    $values = $instance->getPropertyValuesCollection($property);

                    foreach ($values as $value) {
                        if ($value instanceof core_kernel_classes_Resource) {
                            $elementValue    = $element instanceof tao_helpers_form_elements_Readonly
                                ? $value->getLabel()
                                : $value->getUri();
                            $elementValueUri = $value->getUri();
                        } elseif ($value instanceof core_kernel_classes_Literal) {
                            $elementValue    = (string)$value;
                            $elementValueUri = $elementValue;
                        } else {
                            continue;
                        }

                        if ($isList) {
                            $this->fillListElement($element, $property, $elementValueUri);
                        } else {
                            $element->setValue($elementValue);
                        }
                    }
                }

                // don't show empty labels
                if ($element instanceof tao_helpers_form_elements_Label && strlen($element->getRawValue()) === 0) {
                    continue;
                }

                if ($property->getUri() === OntologyRdfs::RDFS_LABEL) {
                    // Label will not be a TAO Property. However, it should
                    // be always first.
                    array_splice($finalElements, 0, 0, [[$element, 1]]);
                } elseif (count($guiOrderPropertyValues = $property->getPropertyValues($guiOrderProperty))) {
                    // get position of this property if it has one.
                    $position = (int) $guiOrderPropertyValues[0];

                    // insert the element at the right place.
                    $i = 0;
                    while ($i < count($finalElements) && ($position >= $finalElements[$i][1] && $finalElements[$i][1] !== null)) {
                        $i++;
                    }

                    array_splice($finalElements, $i, 0, [[$element, $position]]);
                } else {
                    // Unordered properties will go at the end of the form.
                    $finalElements[] = [$element, null];
                }
            }
        }

        // Add elements related to class properties to the form.
        foreach ($finalElements as $element) {
            $this->form->addElement($element[0]);
        }

        //add an hidden elt for the class uri
        $classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
        $classUriElt->setValue(tao_helpers_Uri::encode($clazz->getUri()));
        $this->form->addElement($classUriElt, true);

        if (!is_null($instance)) {
            //add an hidden elt for the instance Uri
            $instanceUriElt = tao_helpers_form_FormFactory::getElement('uri', 'Hidden');
            $instanceUriElt->setValue(tao_helpers_Uri::encode($instance->getUri()));
            $this->form->addElement($instanceUriElt, true);

            $hiddenId = tao_helpers_form_FormFactory::getElement('id', 'Hidden');
            $hiddenId->setValue($instance->getUri());
            $this->form->addElement($hiddenId, true);
        }
    }

    private function fillListElement(
        tao_helpers_form_FormElement $element,
        core_kernel_classes_Property $property,
        string $uri
    ): void {
        $valueService = $this->getValueCollectionService();
        $searchRequest = new ValueCollectionSearchRequest();
        $searchRequest->setValueCollectionUri($property->getRange()->getUri());
        $searchRequest->setUris($uri);
        $valueCollection = $valueService->findAll(
            new ValueCollectionSearchInput($searchRequest)
        );

        foreach ($valueCollection as $value) {
            $element->setValue(
                new ElementValue(tao_helpers_Uri::encode($value->getUri()), $value->getLabel())
            );
        }
    }

    private function getValueCollectionService(): ValueCollectionService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(ValueCollectionService::class);
    }

    private function getElementFactory(): ElementMapFactory
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getServiceLocator()->get(ElementMapFactory::class);
    }

    private function getServiceLocator(): ServiceLocatorInterface
    {
        return ServiceManager::getServiceManager();
    }

    private function isList(core_kernel_classes_Property $property): bool
    {
        $range = $property->getRange();

        if (!$range instanceof core_kernel_classes_Class) {
            return false;
        }

        return $range->isSubClassOf(
            new core_kernel_classes_Class(TaoOntology::CLASS_URI_LIST)
        );
    }
}
