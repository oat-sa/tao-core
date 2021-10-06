<?php

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

use InvalidArgumentException;
use oat\tao\model\Context\AbstractContext;

class DependencyRepositoryContext extends AbstractContext
{
    public const PARAM_LIST_URIS = 'list_uris';
    public const PARAM_DEPENDENCY_LIST_VALUES = 'dependency_list_values';

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_LIST_URIS,
            self::PARAM_DEPENDENCY_LIST_VALUES,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if (is_array($parameterValue)) {
            foreach ($parameterValue as $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Context parameter %s is not valid. The values must be a string.',
                            $parameter
                        )
                    );
                }
            }

            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Context parameter %s is not valid.',
                $parameter
            )
        );
    }
}
