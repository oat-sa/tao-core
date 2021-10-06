<?php

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

use InvalidArgumentException;
use oat\tao\model\Context\AbstractContext;

class DependsOnPropertyValidatorContext extends AbstractContext
{
    public const PARAM_PROPERTIES = 'properties';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_PROPERTIES,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if ($parameter === self::PARAM_PROPERTIES && is_array($parameterValue)) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Context parameter %s is not valid. It must be an array.',
                $parameter
            )
        );
    }
}
