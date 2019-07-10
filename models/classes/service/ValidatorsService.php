<?php

namespace oat\tao\model\service;

use common_Exception;
use common_exception_NotFound;
use core_kernel_classes_Property;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\validator\ExtendedValidatorInterface;
use oat\oatbox\validator\ValidatorInterface;
use oat\tao\helpers\form\ValidationRuleRegistry;

class ValidatorsService extends ConfigurableService
{
    const SERVICE_ID = 'tao/ValidatorsService';

    const VALIDATION_RULE_REGISTRY_PARAM = 'ValidationRuleRegistry';

    /**
     * @param array $values
     * @param core_kernel_classes_Property $property
     * @return ValidatorInterface[]
     * @throws common_exception_NotFound|common_Exception
     */
    public function getPropertyValidators(array $values, core_kernel_classes_Property $property)
    {
        return $this->populateValidators(
            $values,
            $this->getValidationRuleRegistry()->getValidators($property),
            $property->getUri()
        );
    }

    /**
     * @param array $values
     * @param ValidatorInterface[] $validators
     * @param string $propertyUri
     * @return ValidatorInterface[]
     */
    private function populateValidators(array $values, array $validators, $propertyUri)
    {
        foreach($validators as $validator){
            if ($validator instanceof ExtendedValidatorInterface) {
                $validator->populateAdditionValues($values, $propertyUri);
            }
        }
        return $validators;
    }

    /**
     * @return ValidationRuleRegistry
     * @throws common_Exception
     */
    private function getValidationRuleRegistry()
    {
        $validationRuleRegistry = $this->getOption(self::VALIDATION_RULE_REGISTRY_PARAM);

        if (!$validationRuleRegistry instanceof ValidationRuleRegistry) {
            throw new common_Exception(__('Invalid ValidationRuleRegistry param'));
        }

        return $validationRuleRegistry;
    }
}
