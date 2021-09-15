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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Presentation\Web\Factory;

use core_kernel_classes_Property;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Lists\DataAccess\Repository\DependsOnPropertyRepository;

class DependsOnPropertyFormFieldFactory extends ConfigurableService
{
    /** @var tao_helpers_form_FormElement */
    private $element;

    public function withElement(tao_helpers_form_FormElement $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function create(array $options): ?tao_helpers_form_FormElement
    {
        // @TODO Remove feature flag after we can rely on repository output
        if (
            !$this->getFeatureFlagChecker()->isEnabled(
                FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED
            )
        ) {
            return null;
        }

        /** @var core_kernel_classes_Property $property */
        $property = $options['property'];

        $collection = $this->getRepository()->findAll(
            [
                'property' => $property
            ]
        );

        $index = $options['index'] ?? 0;

        $element = $this->element ?? tao_helpers_form_FormFactory::getElement(
            "{$index}_depends-on-property",
            'Combobox'
        );
        $element->addAttribute('class', 'property-depends-on property');
        $element->setDescription(__('Depends on property'));
        $element->setEmptyOption(' --- ' . __('select') . ' --- ');

        if ($collection->count() === 0) {
            $element->disable();
        }

        $options = [];
        $dependsOnProperty = $property->getDependsOnPropertyCollection()->current();
        $dependsOnPropertyUri = $dependsOnProperty !== null
            ? $dependsOnProperty->getUri()
            : null;

        /** @var DependsOnProperty $prop */
        foreach ($collection as $prop) {
            $encodedUri = $prop->getUriEncoded();
            $options[$encodedUri] = $prop->getLabel();

            if ($dependsOnPropertyUri !== null && $prop->getProperty()->getUri() === $dependsOnPropertyUri) {
                $element->setValue($encodedUri);
            }
        }

        asort($options);
        $element->setOptions($options);

        return $element;
    }

    private function getRepository(): DependsOnPropertyRepository
    {
        return $this->getServiceLocator()->get(DependsOnPropertyRepository::class);
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::class);
    }
}
