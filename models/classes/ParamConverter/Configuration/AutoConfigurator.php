<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Configuration;

use ReflectionType;
use ReflectionFunctionAbstract;
use Symfony\Component\HttpFoundation\Request;

class AutoConfigurator
{
    /**
     * @param ParamConverter[] $configurations
     */
    public function configure(ReflectionFunctionAbstract $reflection, Request $request, array &$configurations): void
    {
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            $class = $this->getParamClassByType($type);

            if ($class !== null && $request instanceof $class) {
                continue;
            }

            $name = $parameter->getName();

            if ($type) {
                if (!isset($configurations[$name])) {
                    $configurations[$name] = new ParamConverter($name);
                }

                if ($class !== null && $configurations[$name]->getClass() === null) {
                    $configurations[$name]->setClass($class);
                }

                $configurationClass = $configurations[$name]->getClass();

                if (
                    $configurationClass !== null
                    && $configurations[$name]->getConverter() === null
                    && defined($configurationClass . '::CONVERTER_ID')
                ) {
                    $configurations[$name]->setConverter(
                        constant($configurationClass . '::CONVERTER_ID')
                    );
                }
            }

            if (isset($configurations[$name])) {
                $isOptional = $parameter->isOptional()
                    || $parameter->isDefaultValueAvailable()
                    || ($type && $type->allowsNull());

                $configurations[$name]->setIsOptional($isOptional);
            }
        }
    }

    private function getParamClassByType(?ReflectionType $type): ?string
    {
        return $type !== null && !$type->isBuiltin()
            ? $type->getName()
            : null;
    }
}
