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

use tao_helpers_Uri;
use core_kernel_classes_Property;
use tao_helpers_form_FormElement;
use tao_helpers_form_FormFactory;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Domain\DependsOnProperty;
use oat\tao\model\Lists\Business\Contract\DependsOnPropertyRepositoryInterface;

class DependsOnPropertyFormFieldFactory
{
    /** @var FeatureFlagCheckerInterface */
    private $featureFlagChecker;

    /** @var DependsOnPropertyRepositoryInterface */
    private $dependsOnPropertyRepository;

    /** @var tao_helpers_form_FormElement */
    private $element;

    public function __construct(
        FeatureFlagCheckerInterface $featureFlagChecker,
        DependsOnPropertyRepositoryInterface $dependsOnPropertyRepository
    ) {
        $this->featureFlagChecker = $featureFlagChecker;
        $this->dependsOnPropertyRepository = $dependsOnPropertyRepository;
    }

    public function withElement(tao_helpers_form_FormElement $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function create(array $options): ?tao_helpers_form_FormElement
    {
        // @TODO Remove feature flag after we can rely on repository output
        if (!$this->isListsDependencyEnabled()) {
            return null;
        }

        $element = $this->initElement($options['index'] ?? 0);
        $this->configureElement($element, $options['property']);

        return $element;
    }

    private function isListsDependencyEnabled(): bool
    {
        return $this->featureFlagChecker->isEnabled(
            FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED
        );
    }

    private function initElement(int $index): tao_helpers_form_FormElement
    {
        $element = $this->element ?? tao_helpers_form_FormFactory::getElement(
                $index . '_depends-on-property',
                'Combobox'
            );
        $element->addAttribute('class', 'property-depends-on property');
        $element->setDescription(__('Depends on property'));
        $element->setEmptyOption(' --- ' . __('none') . ' --- ');

        return $element;
    }

    private function configureElement(
        tao_helpers_form_FormElement $element,
        core_kernel_classes_Property $property
    ): void {
        $dependsOnProperty = $property->getDependsOnPropertyCollection()->current();

        if ($dependsOnProperty !== null) {
            $this->configureWithDependency($element, $dependsOnProperty);

            return;
        }

        $this->configureWithPossibleDependencies($element, $property);
    }

    private function configureWithDependency(
        tao_helpers_form_FormElement $element,
        core_kernel_classes_Property $dependsOnProperty
    ): void {
        $dependsOnPropertyEncodedUri = tao_helpers_Uri::encode($dependsOnProperty->getUri());

        $element->setValue($dependsOnPropertyEncodedUri);
        $element->setOptions([$dependsOnPropertyEncodedUri => $dependsOnProperty->getLabel()]);
    }

    private function configureWithPossibleDependencies(
        tao_helpers_form_FormElement $element,
        core_kernel_classes_Property $property
    ): void {
        $collection = $this->dependsOnPropertyRepository->findAll(['property' => $property]);

        if ($collection->count() === 0) {
            $element->disable();

            return;
        }

        $options = [];

        /** @var DependsOnProperty $dependsOnProperty */
        foreach ($collection as $dependsOnProperty) {
            $options[$dependsOnProperty->getUriEncoded()] = $dependsOnProperty->getLabel();
        }

        asort($options);
        $element->setOptions($options);
    }
}
