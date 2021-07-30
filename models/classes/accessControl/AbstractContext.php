<?php

declare(strict_types=1);

namespace oat\tao\model\accessControl;

use InvalidArgumentException;

abstract class AbstractContext
{
    protected $parameters = [];

    public function __construct(array $parameters)
    {
        foreach ($parameters as $parameter => $parameterValue) {
            $this->validateParameter($parameter, $parameterValue);

            $this->parameters[$parameter] = $parameterValue;
        }
    }

    public function getParameter(string $parameter)
    {
        if (!in_array($parameter, $this->getSupportedParameters(), true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Context parameter %s is not supported.',
                    $parameter
                )
            );
        }

        return $this->parameters[$parameter] ?? null;
    }

    abstract protected function getSupportedParameters(): array;

    /**
     * @param $parameterValue
     */
    abstract protected function validateParameter(string $parameter, $parameterValue): void;
}
