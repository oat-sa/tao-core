<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Request;

use Throwable;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;

class QueryParamConverter implements ParamConverterInterface
{
    public function getPriority(): int
    {
        return 0;
    }

    public function getName(): string
    {
        return 'oat.tao.param_converter.query';
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $queryParameters = $request->query->all();
            $class = $configuration->getClass();

            $constructor = new ReflectionMethod($class, '__construct');
            $constructorArgs = [];

            foreach ($constructor->getParameters() as $constructorParameter) {
                $constructorParameterName = $constructorParameter->getName();

                if (array_key_exists($constructorParameterName, $queryParameters)) {
                    $constructorArgs[$constructorParameterName] = $queryParameters[$constructorParameterName];
                    unset($queryParameters[$constructorParameterName]);
                }
            }

            $instance = (new ReflectionClass($class))->newInstanceArgs($constructorArgs);

            foreach ($queryParameters as $queryParameter => $value) {
                if (method_exists($instance, 'set' . $queryParameter)) {
                    $instance->{'set' . $queryParameter}($value);
                } elseif (property_exists($instance, $queryParameter)) {
                    $instance->$queryParameter = $value;
                }
            }

            $request->attributes->set($configuration->getName(), $instance);
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getConverter() === $this->getName() && $configuration->getClass() !== null;
    }
}
