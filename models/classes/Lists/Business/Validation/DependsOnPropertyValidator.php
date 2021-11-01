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

use tao_helpers_form_Form;
use tao_helpers_form_FormElement;
use oat\generis\model\data\Ontology;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\elements\FormElementAware;
use oat\tao\helpers\form\Decorator\ElementDecorator;
use oat\tao\helpers\form\validators\CrossElementEvaluationAware;
use oat\tao\helpers\form\validators\PreliminaryValidationInterface;
use oat\tao\model\Lists\Business\Domain\DependencyRepositoryContext;
use oat\tao\model\Lists\Business\Contract\DependencyRepositoryInterface;

class DependsOnPropertyValidator implements
    ValidatorInterface,
    FormElementAware,
    CrossElementEvaluationAware,
    PreliminaryValidationInterface
{
    /** @var DependencyRepositoryInterface */
    private $dependencyRepository;

    /** @var Ontology */
    private $ontology;

    /** @var array */
    private $options = [];

    /** @var string */
    private $message;

    /** @var tao_helpers_form_FormElement */
    private $element;

    /** @var ElementDecorator */
    private $elementDecorator;

    public function __construct(DependencyRepositoryInterface $dependencyRepository, Ontology $ontology)
    {
        $this->dependencyRepository = $dependencyRepository;
        $this->ontology = $ontology;
    }

    public function isPreValidationRequired(): bool
    {
        return true;
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
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return bool
     */
    public function evaluate($values)
    {
        $providedValues = $this->elementDecorator->getListValues();
        $invalidValues = $this->getInvalidValues($providedValues);
        $isValid = empty($invalidValues);

        if (!$isValid) {
            $this->element->setInvalidValues($invalidValues);
            $this->message = __('The selected value(s) must be compatible with the primary property.');
        }

        return $isValid;
    }

    public function setElement(tao_helpers_form_FormElement $element): void
    {
        $this->element = $element;
    }

    public function acknowledge(tao_helpers_form_Form $form): void
    {
        $this->elementDecorator = new ElementDecorator($this->ontology, $form, $this->element);
    }

    private function getInvalidValues(array $providedValues): array
    {
        $validValues = [];

        foreach ($this->elementDecorator->getPrimaryElementsDecorators() as $elementDecorator) {
            if (empty($elementDecorator->getListValues())) {
                continue;
            }

            $context = $this->createContext(
                $elementDecorator->getRangeClass()->getUri(),
                $elementDecorator->getListValues()
            );
            $childListItemsUris = $this->dependencyRepository->findChildListItemsUris($context);

            $validValues = array_merge($validValues, array_intersect($providedValues, $childListItemsUris));
        }

        return array_diff($providedValues, $validValues);
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
