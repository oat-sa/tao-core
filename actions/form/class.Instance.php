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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *               2017-2021 (update and modification) Open Assessment Technologies SA;
 */

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\form\ElementMapFactory;
use oat\tao\helpers\form\elements\ElementValue;
use oat\tao\model\form\DataProvider\FormDataProviderInterface;
use oat\tao\model\form\DataProvider\ProxyFormDataProvider;
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
    use OntologyAwareTrait;

    public const EXCLUDED_PROPERTIES = 'excludedProperties';
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
        $class = $this->getClazz();
        $instance = $this->getInstance();

        // Guess language
        try {
            $language = $this->options['lang'] ?? common_session_SessionManager::getSession()->getInterfaceLanguage();
        } catch (common_exception_Error $exception) {
            $language = DEFAULT_LANG;
        }

        $topClass = $this->getTopClazz();

        if ($instance) {
            $this->getFormDataProvider()->preloadFormData(
                $class->getUri(),
                $topClass->getUri(),
                $instance->getUri(),
                $language
            );
        }

        //get the list of properties to set in the form
        $propertyCandidates = tao_helpers_form_GenerisFormFactory::getDefaultProperties();
        $classProperties = $this->getFormDataProvider()->getClassProperties($class, $topClass);

        $propertyCandidates = array_merge($propertyCandidates, $classProperties);

        $additionalProperties = (isset($this->options['additionalProperties'])
            && is_array($this->options['additionalProperties']))
                ? $this->options['additionalProperties']
                : [];
        if (!empty($additionalProperties)) {
            $propertyCandidates = array_merge($propertyCandidates, $additionalProperties);
        }

        $excludedProperties = (isset($this->options[self::EXCLUDED_PROPERTIES])
            && is_array($this->options[self::EXCLUDED_PROPERTIES]))
                ? $this->options[self::EXCLUDED_PROPERTIES]
                : [];
        $editedProperties = [];
        foreach ($propertyCandidates as $property) {
            if (!isset($editedProperties[$property->getUri()]) && !in_array($property->getUri(), $excludedProperties)) {
                $editedProperties[$property->getUri()] = $property;
            }
        }

        $finalElements = [];
        foreach ($editedProperties as $property) {
            $property->feedFromData(...$this->getFormDataProvider()->getDataToFeedProperty($property));

            $widget = $property->getWidget();
            if ($widget === null || $widget instanceof core_kernel_classes_Literal) {
                continue;
            }

            //map properties widgets to form elements
            $elementFactory = $this->getElementFactory();

            if ($instance instanceof core_kernel_classes_Resource) {
                $elementFactory->withInstance($instance);
            }

            $element = $elementFactory->create($property, $language);

            if ($element !== null) {
                if ($instance) {
                    $propertyInstanceValues = $this
                        ->getFormDataProvider()
                        ->getPropertyInstanceValues($property, $instance, $element);
                    foreach ($propertyInstanceValues as $valueData) {
                        if ($this->getFormDataProvider()->isPropertyList($property)) {
                            $element->setValue(
                                new ElementValue(tao_helpers_Uri::encode($valueData[0]), $valueData[1])
                            );
                        } else {
                            $element->setValue($valueData[0]);
                        }
                    }
                }

                if ($this->isEmptyLabel($element)) {
                    continue;
                }

                if ($property->getUri() === OntologyRdfs::RDFS_LABEL) {
                    // Label will not be a TAO Property. However, it should be always first.
                    array_splice($finalElements, 0, 0, [[$element, 1]]);
                } elseif (count($guiOrder = $this->getFormDataProvider()->getPropertyGUIOrder($property))) {
                    // get position of this property if it has one.
                    $position = (int) $guiOrder[0];

                    // insert the element at the right place.
                    $i = 0;
                    while (
                        $i < count($finalElements)
                        && ($position >= $finalElements[$i][1] && $finalElements[$i][1] !== null)
                    ) {
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

        //add a hidden elt for the class uri
        $classUriElt = tao_helpers_form_FormFactory::getElement('classUri', 'Hidden');
        $classUriElt->setValue(tao_helpers_Uri::encode($class->getUri()));
        $this->form->addElement($classUriElt, true);

        if ($instance) {
            //add a hidden elt for the instance Uri
            $instanceUriElt = tao_helpers_form_FormFactory::getElement('uri', 'Hidden');
            $instanceUriElt->setValue(tao_helpers_Uri::encode($instance->getUri()));
            $this->form->addElement($instanceUriElt, true);

            $hiddenId = tao_helpers_form_FormFactory::getElement('id', 'Hidden');
            $hiddenId->setValue($instance->getUri());
            $this->form->addElement($hiddenId, true);
        }
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

    private function getFormDataProvider(): FormDataProviderInterface
    {
        return $this->getServiceLocator()->getContainer()->get(ProxyFormDataProvider::class)->getProvider();
    }

    private function isEmptyLabel($element): bool
    {
        return $element instanceof tao_helpers_form_elements_Label
            && empty($element->getRawValue());
    }
}
