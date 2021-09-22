<?php

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

use InvalidArgumentException;
use core_kernel_classes_Property;
use oat\tao\model\Context\AbstractContext;

class DependentPropertiesRepositoryContext extends AbstractContext
{
    public const PARAM_PROPERTY = 'property';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_PROPERTY,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if ($parameter === self::PARAM_PROPERTY && !$parameterValue instanceof core_kernel_classes_Property) {
            throw new InvalidArgumentException(
                sprintf(
                    'Context parameter %s is not valid. It must be an instance of %s.',
                    $parameter,
                    core_kernel_classes_Property::class
                )
            );
        }
    }
}
