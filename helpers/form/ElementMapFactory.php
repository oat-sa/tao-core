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
 * Copyright (c) 2020-2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\helpers\form;

use common_Logger;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\form\elements\TreeAware;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\form\DataProvider\FormDataProviderInterface;
use oat\tao\model\form\DataProvider\ProxyFormDataProvider;
use Psr\Container\ContainerInterface;
use tao_helpers_Context;
use tao_helpers_form_elements_AsyncFile as AsyncFile;
use tao_helpers_form_elements_Authoring as Authoring;
use tao_helpers_form_elements_GenerisAsyncFile as GenerisAsyncFile;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;
use tao_helpers_Uri;

class ElementMapFactory extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'tao/ElementMapFactory';

    /** @var core_kernel_classes_Resource */
    private $instance;

    public function withInstance(core_kernel_classes_Resource $instance): self
    {
        $this->instance = $instance;

        return $this;
    }

    public function create(
        core_kernel_classes_Property $property,
        string $language = DEFAULT_LANG
    ): ?tao_helpers_form_FormElement {

        // Create the element from the right widget
        $property->feed();

        $widgetResource = $property->getWidget();
        if (null === $widgetResource) {
            return null;
        }

        $widgetUri   = $widgetResource->getUri();
        $propertyUri = $property->getUri();
        // Authoring widget is not used in standalone mode
        if (
            $widgetUri === Authoring::WIDGET_ID
            && tao_helpers_Context::check('STANDALONE_MODE')
        ) {
            return null;
        }

        // Horrible hack to fix file widget
        if ($widgetUri === AsyncFile::WIDGET_ID) {
            $widgetResource = new core_kernel_classes_Resource(GenerisAsyncFile::WIDGET_ID);
        }

        $element = tao_helpers_form_FormFactory::getElementByWidget(
            tao_helpers_Uri::encode($propertyUri),
            $widgetResource
        );

        if (null === $element) {
            return null;
        }

        $isListsDependencyEnabled = $this->getFeatureFlagChecker()->isEnabled(
            FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED
        );

        $parentProperty = null;

        if ($isListsDependencyEnabled) {
            $parentProperty = $this->getParentProperty($property);

            if ($parentProperty) {
                $element->addAttribute('data-depends-on-property', tao_helpers_Uri::encode($parentProperty->getUri()));
            }
        }

        if ($element->getWidget() !== $widgetUri) {
            common_Logger::w(sprintf(
                'Widget definition differs from implementation: %s != %s',
                $element->getWidget(),
                $widgetUri
            ));

            return null;
        }

        $description = $this->getFormDataProvider()->getDescriptionFromTranslatedPropertyLabel($property, $language);

        $element->setDescription($description);

        if (method_exists($element, 'setOptions')) {
            // Multi elements use the property range as options
            $range = $property->getRange();

            if ($range !== null) {
                if ($element instanceof TreeAware) {
                    $options = $element->rangeToTree(
                        $propertyUri === OntologyRdfs::RDFS_RANGE
                            ? new core_kernel_classes_Class(OntologyRdfs::RDFS_RESOURCE)
                            : $range
                    );
                } else {
                    if ($this->getFormDataProvider()->isPropertyList($property)) {
                        $options = $this
                            ->getFormDataProvider()
                            ->getPropertyListElementOptions($property, $parentProperty, $this->instance);
                    } else {
                        $options = $this->getFormDataProvider()->getPropertyNotListElementOptions($property);
                    }

                    foreach ($options as [$uri, $label]) {
                        $options[$uri] = $label;
                    }

                    // Set the default value to an empty space
                    if (method_exists($element, 'setEmptyOption')) {
                        $element->setEmptyOption(' ');
                    }
                }

                // Complete the options listing
                $element->setOptions($options);
            }
        }

        if ($this->isBlockedForModification($property)) {
            $element->disable();
        }

        foreach ($this->getFormDataProvider()->getPropertyValidators($property) as $validator) {
            $element->addValidator($validator);
        }

        return $element;
    }

    private function isBlockedForModification(core_kernel_classes_Property $property): bool
    {
        if ($this->getFeatureFlagChecker()->isEnabled('FEATURE_FLAG_STATISTIC_METADATA_IMPORT')) {
            return $property->isStatistical();
        }

        return false;
    }

    private function getParentProperty(core_kernel_classes_Property $property): ?core_kernel_classes_Property
    {
        $collection = $property->getDependsOnPropertyCollection();

        return $collection->offsetExists(0)
            ? $collection->offsetGet(0)
            : null;
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getContainer()->get(FeatureFlagChecker::class);
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getServiceManager()->getContainer();
    }

    public function getFormDataProvider(): FormDataProviderInterface
    {
        return $this->getContainer()->get(ProxyFormDataProvider::class)->getProvider();
    }
}
