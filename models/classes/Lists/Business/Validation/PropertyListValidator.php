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

use InvalidArgumentException;
use oat\generis\model\data\Ontology;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\Decorator\ElementDecorator;
use oat\tao\helpers\form\elements\FormElementAware;
use oat\tao\helpers\form\validators\CrossElementEvaluationAware;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\Lists\Business\Specification\PrimaryPropertySpecification;
use oat\tao\model\Lists\Business\Specification\PropertySpecificationContext;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use tao_helpers_form_Form;
use tao_helpers_form_FormElement;

class PropertyListValidator implements ValidatorInterface, CrossElementEvaluationAware, FormElementAware
{
    /** @var Ontology */
    private $ontology;

    /** @var PrimaryPropertySpecification */
    private $primaryPropertySpecification;

    /** @var SecondaryPropertySpecification */
    private $secondaryPropertySpecification;

    /** @var tao_helpers_form_FormElement */
    private $element;

    /** @var array */
    private $options;

    /** @var ElementDecorator */
    private $elementDecorator;

    /** @var RemoteListClassSpecification */
    private $remoteListClassSpecification;

    /** @var FeatureFlagCheckerInterface */
    private $featureFlagChecker;

    public function __construct(
        Ontology $ontology,
        PrimaryPropertySpecification $primaryPropertySpecification,
        SecondaryPropertySpecification $secondaryPropertySpecification,
        RemoteListClassSpecification $remoteListClassSpecification,
        FeatureFlagCheckerInterface $featureFlagChecker
    ) {
        $this->ontology = $ontology;
        $this->primaryPropertySpecification = $primaryPropertySpecification;
        $this->secondaryPropertySpecification = $secondaryPropertySpecification;
        $this->remoteListClassSpecification = $remoteListClassSpecification;
        $this->featureFlagChecker = $featureFlagChecker;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::class;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return __('Invalid value');
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function evaluate($values)
    {
        if (
            !$this->featureFlagChecker->isEnabled(FeatureFlagCheckerInterface::FEATURE_FLAG_LISTS_DEPENDENCY_ENABLED)
        ) {
            return true;
        }

        if (!$this->isPrimaryOrSecondary()) {
            return true;
        }

        return $this->isRemoteListForPrimaryOrSecondary();
    }

    public function acknowledge(tao_helpers_form_Form $form): void
    {
        $this->elementDecorator = new ElementDecorator($this->ontology, $form, $this->element);
    }

    public function withElementDecorator(ElementDecorator $elementDecorator): void
    {
        $this->elementDecorator = $elementDecorator;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function setElement(tao_helpers_form_FormElement $element): void
    {
        $this->element = $element;
    }

    private function isRemoteListForPrimaryOrSecondary(): bool
    {
        $class = $this->elementDecorator->getRangeClass();

        return $class && $this->remoteListClassSpecification->isSatisfiedBy($class);
    }

    private function isPrimaryOrSecondary(): bool
    {
        $property = $this->elementDecorator->getProperty();

        return $this->primaryPropertySpecification->isSatisfiedBy($property) ||
            $this->secondaryPropertySpecification->isSatisfiedBy(
                new PropertySpecificationContext(
                    [
                        PropertySpecificationContext::PARAM_PROPERTY => $property,
                        PropertySpecificationContext::PARAM_FORM_INDEX => $this->elementDecorator->getIndex(),
                        PropertySpecificationContext::PARAM_FORM_DATA => $this->elementDecorator->getFormData()
                    ]
                )
            );
    }
}
