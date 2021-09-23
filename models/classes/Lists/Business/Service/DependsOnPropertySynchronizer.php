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

namespace oat\tao\model\Lists\Business\Service;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\Context\ContextInterface;
use oat\tao\helpers\form\ValidationRuleRegistry;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use oat\tao\model\Lists\DataAccess\Repository\DependentPropertiesRepository;
use oat\tao\model\Lists\Business\Domain\DependentPropertiesRepositoryContext;
use oat\tao\model\Lists\Business\Domain\DependsOnPropertySynchronizerContext;
use oat\tao\model\Lists\Business\Contract\DependsOnPropertySynchronizerInterface;
use oat\tao\model\Lists\Business\Contract\DependentPropertiesRepositoryInterface;

class DependsOnPropertySynchronizer extends ConfigurableService implements DependsOnPropertySynchronizerInterface
{
    use OntologyAwareTrait;

    private const REQUIRED_VALIDATION_RULES = ['notEmpty'];

    /** @var DependentPropertiesRepositoryInterface */
    private $dependentPropertiesRepository;

    /** @var array[] */
    private $dependentProperties = [];

    /** @var array[] */
    private $rulesToRemove = [];

    public function sync(ContextInterface $context): void
    {
        $isListsDependencyEnabled = $this->getFeatureFlagChecker()->isEnabled(
            FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED
        );

        if (!$isListsDependencyEnabled) {
            return;
        }

        /** @var core_kernel_classes_Property[] $properties */
        $properties = $context->getParameter(DependsOnPropertySynchronizerContext::PARAM_PROPERTIES, []);
        $validationRuleProperty = $this->getProperty(ValidationRuleRegistry::PROPERTY_VALIDATION_RULE);

        foreach ($this->incrementWithParents($properties) as $property) {
            foreach ($this->getDependentProperties($property) as $dependentProperty) {
                $this->bindProperties(
                    $dependentProperty,
                    [
                        ValidationRuleRegistry::PROPERTY_VALIDATION_RULE => $this->getNewValidationRules(
                            $property,
                            $dependentProperty,
                            $validationRuleProperty
                        ),
                    ]
                );
            }
        }
    }

    /**
     * @param core_kernel_classes_Property[] $properties
     */
    private function incrementWithParents(array $properties): array
    {
        $parentProperties = [];
        $initialProperties = [];

        foreach ($properties as $property) {
            $initialProperties[] = $property->getUri();
            $parentProperty = $property->getDependsOnPropertyCollection()->current();

            if ($parentProperty) {
                $parentProperties[$parentProperty->getUri()] = $parentProperty;
            }
        }

        foreach ($parentProperties as $parentPropertyUri => $parentProperty) {
            if (in_array($parentPropertyUri, $initialProperties, true)) {
                unset($parentProperties[$parentPropertyUri]);
            }
        }

        return array_merge($properties, $parentProperties);
    }

    /**
     * @return core_kernel_classes_Resource[]
     */
    private function getDependentProperties(core_kernel_classes_Property $property): array
    {
        $propertyUri = $property->getUri();

        if (!isset($this->dependentProperties[$propertyUri])) {
            $this->dependentProperties[$propertyUri] = $this->getDependentPropertiesRepository()->findAll(
                new DependentPropertiesRepositoryContext([
                    DependentPropertiesRepositoryContext::PARAM_PROPERTY => $property,
                ])
            );
        }

        return $this->dependentProperties[$propertyUri];
    }

    private function getNewValidationRules(
        core_kernel_classes_Property $property,
        core_kernel_classes_Resource $dependentProperty,
        core_kernel_classes_Property $validationRuleProperty
    ): array {
        return array_diff(
            $dependentProperty->getPropertyValues($validationRuleProperty),
            $this->getRulesToRemove($property, $validationRuleProperty)
        );
    }

    private function getRulesToRemove(
        core_kernel_classes_Property $property,
        core_kernel_classes_Property $validationRuleProperty
    ): array {
        $propertyUri = $property->getUri();

        if (!isset($this->rulesToRemove[$propertyUri])) {
            $propertyValidationRules = $property->getPropertyValues($validationRuleProperty);
            $this->rulesToRemove[$propertyUri] = array_diff(
                self::REQUIRED_VALIDATION_RULES,
                $propertyValidationRules
            );
        }

        return $this->rulesToRemove[$propertyUri];
    }

    private function bindProperties(core_kernel_classes_Resource $property, array $values): void
    {
        $binder = new tao_models_classes_dataBinding_GenerisInstanceDataBinder($property);
        $binder->bind($values);
    }

    private function getFeatureFlagChecker(): FeatureFlagCheckerInterface
    {
        return $this->getServiceLocator()->get(FeatureFlagChecker::class);
    }

    private function getDependentPropertiesRepository(): DependentPropertiesRepositoryInterface
    {
        if (!isset($this->dependentPropertiesRepository)) {
            $this->dependentPropertiesRepository = $this->getServiceLocator()->get(
                DependentPropertiesRepository::class
            );
        }

        return $this->dependentPropertiesRepository;
    }
}
