<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\EventListener;

use ReflectionType;
use ReflectionMethod;
use ReflectionFunctionAbstract;
use oat\tao\model\ParamConverter\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use oat\tao\model\ParamConverter\Event\ParamConverterEvent;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;
use oat\tao\model\ParamConverter\Context\ParamConverterListenerContext;
use oat\tao\model\ParamConverter\Request\ParamConverterManager;

class ParamConverterListener implements ListenerInterface
{
    public const REQUEST_ATTRIBUTE_CONVERTERS = '_converters';

    /** @var ParamConverterManager */
    private $paramConverterManager;

    /** @var bool */
    private $autoConvert;

    public function __construct(ParamConverterManager $paramConverterManager, bool $autoConvert = true)
    {
        $this->paramConverterManager = $paramConverterManager;
        $this->autoConvert = $autoConvert;
    }

    public function handleEvent(Event $event): void
    {
        if (!$event instanceof ParamConverterEvent) {
            return;
        }

        $context = $event->getContext();
        /** @var Request $request */
        $request = $context->getParameter(ParamConverterListenerContext::PARAM_REQUEST);

        $configurations = [];
        $requestConfigurations = $request->attributes->get('_converters', []);

        if (!is_array($requestConfigurations)) {
            $requestConfigurations = [$requestConfigurations];
        }

        /** @var ParamConverter $requestConfiguration */
        foreach ($requestConfigurations as $requestConfiguration) {
            $configurations[$requestConfiguration->getName()] = $requestConfiguration;
        }

        $controller = $context->getParameter(ParamConverterListenerContext::PARAM_CONTROLLER);
        $method = $context->getParameter(ParamConverterListenerContext::PARAM_METHOD);

        // automatically apply conversion for non-configured objects
        if ($this->autoConvert) {
            if (is_callable([$controller, $method])) {
                $reflection = new ReflectionMethod($controller, $method);
                $configurations = $this->autoConfigure($reflection, $request, $configurations);
            }
        }

        $this->paramConverterManager->apply($request, $configurations);
    }

    /**
     * @param ParamConverter[] $configurations
     *
     * @return ParamConverter[]
     */
    private function autoConfigure(
        ReflectionFunctionAbstract $reflection,
        Request $request,
        array $configurations
    ): array {
        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $class = $this->getParamClassByType($type);

            if ($class !== null && $request instanceof $class) {
                continue;
            }

            $name = $param->getName();

            if ($type) {
                if (!isset($configurations[$name])) {
                    $configuration = new ParamConverter([]);
                    $configuration->setName($name);

                    $configurations[$name] = $configuration;
                }

                if ($class !== null && $configurations[$name]->getClass() === null) {
                    $configurations[$name]->setClass($class);
                }
            }

            if (isset($configurations[$name])) {
                $isOptional = $param->isOptional()
                    || $param->isDefaultValueAvailable()
                    || ($type && $type->allowsNull());

                $configurations[$name]->setIsOptional($isOptional);
            }
        }

        return $configurations;
    }

    private function getParamClassByType(?ReflectionType $type): ?string
    {
        return $type !== null && !$type->isBuiltin()
            ? $type->getName()
            : null;
    }
}
