<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Context;

use InvalidArgumentException;
use oat\tao\model\Context\AbstractContext;
use Symfony\Component\HttpFoundation\Request;

class ParamConverterListenerContext extends AbstractContext
{
    public const PARAM_REQUEST = 'request';
    public const PARAM_CONTROLLER = 'controller';
    public const PARAM_METHOD = 'method';

    public function __construct(array $parameters)
    {
        $this->checkRequiredParameters($parameters);

        parent::__construct($parameters);
    }

    protected function getRequiredParameters(): array
    {
        return [
            self::PARAM_REQUEST,
            self::PARAM_CONTROLLER,
            self::PARAM_METHOD,
        ];
    }

    protected function getSupportedParameters(): array
    {
        return [
            self::PARAM_REQUEST,
            self::PARAM_CONTROLLER,
            self::PARAM_METHOD,
        ];
    }

    protected function validateParameter(string $parameter, $parameterValue): void
    {
        if ($parameter === self::PARAM_REQUEST && $parameterValue instanceof Request) {
            return;
        }

        if (
            $parameter === self::PARAM_CONTROLLER
            && (is_string($parameterValue) || is_object($parameterValue))
        ) {
            return;
        }

        if ($parameter === self::PARAM_METHOD && is_string($parameterValue)) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Context parameter %s is not valid.',
                $parameter
            )
        );
    }

    private function checkRequiredParameters(array $parameters): void
    {
        $missedParameters = array_diff($this->getRequiredParameters(), array_keys($parameters));

        if (!empty($missedParameters)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The following required context parameters are missing: %s.',
                    implode(', ', $missedParameters)
                )
            );
        }
    }
}
