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

namespace oat\tao\model\Lists\Business\Validation;

use tao_helpers_Uri;
use tao_helpers_form_Form;
use InvalidArgumentException;
use core_kernel_classes_Property;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\elements\ElementValue;
use oat\tao\model\Lists\Business\Domain\DependencyRepositoryContext;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;
use oat\tao\helpers\form\validators\CrossPropertyEvaluationAwareInterface;

class DependsOnPropertyValidator implements ValidatorInterface, CrossPropertyEvaluationAwareInterface
{
    /** @var DependencyRepositoryInterface */
    private $dependencyRepository;

    /** @var array */
    private $options = [];

    /** @var core_kernel_classes_Property */
    private $property;

    /** @var DependencyRepositoryContext[] */
    private $dependencyRepositoryContexts = [];

    public function __construct(DependencyRepositoryInterface $dependencyRepository)
    {
        $this->dependencyRepository = $dependencyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return __('Invalid value');
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        throw new InvalidArgumentException(
            sprintf(
                'Message for validator %s cannot be set.',
                self::class
            )
        );
    }

    /**
     * @param string|array $values
     *
     * @return bool
     */
    public function evaluate($values)
    {
        $values = $this->prepareValues($values);
        $providedValidValues = [];

        foreach ($this->dependencyRepositoryContexts as $context) {
            $childListItemsUris = $this->dependencyRepository->findChildListItemsUris($context);
            $providedValidValues = array_merge($providedValidValues, array_intersect($values, $childListItemsUris));
        }

        return empty(array_diff($values, $providedValidValues));
    }

    public function setProperty(core_kernel_classes_Property $property): void
    {
        $this->property = $property;
    }

    public function acknowledge(tao_helpers_form_Form $form): void
    {
        foreach ($this->property->getDependsOnPropertyCollection() as $parentProperty) {
            $element = $form->getElement(tao_helpers_Uri::encode($parentProperty->getUri()));

            if ($element === null) {
                continue;
            }

            $this->dependencyRepositoryContexts[] = $this->createContext(
                $parentProperty->getRange()->getUri(),
                explode(',', $element->getInputValue() ?? '')
            );
        }
    }

    /**
     * @param string|array $values
     */
    private function prepareValues($values): array
    {
        if (is_string($values)) {
            $values = [trim($values)];
        }

        if (!is_array($values)) {
            return [];
        }

        $values = array_map(
            static function ($value) {
                return $value instanceof ElementValue
                    ? $value->getUri()
                    : $value;
            },
            $values
        );

        return array_filter($values);
    }

    private function createContext(string $rangeUri, array $listValues): DependencyRepositoryContext
    {
        return new DependencyRepositoryContext(
            [
                DependencyRepositoryContext::PARAM_LIST_URIS => [$rangeUri],
                DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES => $listValues,
            ]
        );
    }
}
