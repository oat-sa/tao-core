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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\helpers\form;

use common_Logger;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\form\elements\TreeAware;
use oat\tao\model\Lists\Business\Domain\ValueCollectionSearchRequest;
use oat\tao\model\Lists\Business\Input\ValueCollectionSearchInput;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\TaoOntology;
use tao_helpers_Context;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;
use tao_helpers_Uri;

class ElementMapFactory extends ConfigurableService
{
    public const SERVICE_ID = 'tao/ElementMapFactory';

    public function create(core_kernel_classes_Property $property): ?tao_helpers_form_FormElement
    {
        //create the element from the right widget
        $property->feed();

        $widgetResource = $property->getWidget();
        if (null === $widgetResource) {
            return null;
        }

        //authoring widget is not used in standalone mode
        if (
            $widgetResource->getUri() === 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#Authoring'
            && tao_helpers_Context::check('STANDALONE_MODE')
        ) {
            return null;
        }

        // horrible hack to fix file widget
        if ($widgetResource->getUri() === 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#AsyncFile') {
            $widgetResource = new core_kernel_classes_Resource(
                'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#GenerisAsyncFile'
            );
        }

        $element = tao_helpers_form_FormFactory::getElementByWidget(
            tao_helpers_Uri::encode($property->getUri()),
            $widgetResource
        );

        if (null === $element) {
            return null;
        }

        if ($element->getWidget() !== $widgetResource->getUri()) {
            common_Logger::w(
                'Widget definition differs from implementation: ' . $element->getWidget(
                ) . ' != ' . $widgetResource->getUri()
            );

            return null;
        }

        //use the property label as element description
        $propDesc = (trim($property->getLabel()) !== '')
            ? $property->getLabel()
            : str_replace(LOCAL_NAMESPACE, '', $property->getUri());

        $element->setDescription($propDesc);

        //multi elements use the property range as options
        if (method_exists($element, 'setOptions')) {
            $range = $property->getRange();

            if ($range !== null) {
                $options = [];

                if ($element instanceof TreeAware) {
                    $sortedOptions = $element->rangeToTree(
                        $property->getUri() === OntologyRdfs::RDFS_RANGE
                            ? new core_kernel_classes_Class(OntologyRdfs::RDFS_RESOURCE)
                            : $range
                    );
                } else {
                    if ($this->isList($range)) {
                        $cv = $this->getValueCollectionService();
                        $request = new ValueCollectionSearchRequest();
                        $request->setValueCollectionUri($range->getUri());
                        $values = $cv->findAll(
                            new ValueCollectionSearchInput(
                                $request
                            )
                        );

                        foreach ($values as $value) {
                            $encodedUri = tao_helpers_Uri::encode($value->getUri());
                            $options[$encodedUri] = [$encodedUri, $value->getLabel()];
                        }
                    } else {
                        foreach ($range->getInstances(true) as $rangeInstance) {
                            $level = $rangeInstance->getOnePropertyValue(
                                new core_kernel_classes_Property(TaoOntology::PROPERTY_LIST_LEVEL)
                            );
                            if (null === $level) {
                                $encodedUri = tao_helpers_Uri::encode($rangeInstance->getUri());
                                $options[$encodedUri] = [$encodedUri, $rangeInstance->getLabel()];
                            } else {
                                $level = ($level instanceof core_kernel_classes_Resource)
                                    ? $level->getUri()
                                    : (string)$level;
                                $options[$level] = [
                                    tao_helpers_Uri::encode($rangeInstance->getUri()),
                                    $rangeInstance->getLabel()
                                ];
                            }
                        }
                    }
                    ksort($options);
                    $sortedOptions = [];
                    foreach ($options as $id => $values) {
                        $sortedOptions[$values[0]] = $values[1];
                    }
                    //set the default value to an empty space
                    if (method_exists($element, 'setEmptyOption')) {
                        $element->setEmptyOption(' ');
                    }
                }

                //complete the options listing
                $element->setOptions($sortedOptions);
            }
        }

        foreach (ValidationRuleRegistry::getRegistry()->getValidators($property) as $validator) {
            $element->addValidator($validator);
        }

        return $element;
    }

    private function isList($range): bool
    {
        if (!$range->isClass()) {
            return false;
        }

        return $range->isSubClassOf(
            new core_kernel_classes_Class(TaoOntology::CLASS_URI_LIST)
        );
    }

    private function getValueCollectionService(): ValueCollectionService
    {
        return $this->getServiceLocator()->get(ValueCollectionService::class);
    }
}
