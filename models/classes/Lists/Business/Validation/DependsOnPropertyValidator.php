<?php

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Validation;

use tao_helpers_Uri;
use tao_helpers_form_Form;
use InvalidArgumentException;
use core_kernel_classes_Property;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\elements\ElementValue;
use oat\tao\helpers\form\validators\PropertyValidatorInterface;
use oat\tao\helpers\form\validators\CrossElementEvaluationAware;
use oat\tao\model\Lists\Business\Domain\DependencyRepositoryContext;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;

class DependsOnPropertyValidator implements ValidatorInterface, PropertyValidatorInterface, CrossElementEvaluationAware
{
    /** @var DependencyRepositoryInterface */
    private $dependencyRepository;

    /** @var array */
    private $options = [];

    /** @var core_kernel_classes_Property */
    private $property;

    /** @var array */
    private $parentPropertiesValues = [];

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
     */
    public function evaluate($values)
    {
        $values = $this->valuesToArray($values);
        $providedValidValues = [];

        foreach ($this->parentPropertiesValues as $parentListItemValues) {
            $childListItemsUris = $this->dependencyRepository->findChildListItemsUris(
                $this->createContext($parentListItemValues)
            );

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

            $this->parentPropertiesValues[] = [
                'range' => $parentProperty->getRange()->getUri(),
                'values' => explode(
                    ',',
                    $element->getInputValue() ?? ''
                )
            ];
        }
    }

    private function valuesToArray($values): array
    {
        if (is_string($values)) {
            $values = [$values];
        }

        if (is_array($values)) {
            $values = array_map(static function ($value) {
                return $value instanceof ElementValue
                    ? $value->getUri()
                    : $value;
            }, $values);
        }

        return $values;
    }

    private function createContext(array $parentListItemValues): DependencyRepositoryContext
    {
        return new DependencyRepositoryContext(
            [
                DependencyRepositoryContext::PARAM_LIST_URIS => [$parentListItemValues['range']],
                DependencyRepositoryContext::PARAM_DEPENDENCY_LIST_VALUES => $parentListItemValues['values'],
            ]
        );
    }
}
