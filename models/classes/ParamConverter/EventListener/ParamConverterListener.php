<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\EventListener;

use ReflectionMethod;
use oat\tao\model\ParamConverter\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use oat\tao\model\ParamConverter\Event\ParamConverterEvent;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;
use oat\tao\model\ParamConverter\Manager\ParamConverterManager;
use oat\tao\model\ParamConverter\Configuration\AutoConfigurator;
use oat\tao\model\ParamConverter\Context\ParamConverterListenerContext;

class ParamConverterListener implements ListenerInterface
{
    public const REQUEST_ATTRIBUTE_CONVERTERS = '_converters';

    /** @var AutoConfigurator */
    private $autoConfigurator;

    /** @var ParamConverterManager */
    private $paramConverterManager;

    /** @var bool */
    private $autoConvert;

    public function __construct(
        AutoConfigurator $autoConfigurator,
        ParamConverterManager $paramConverterManager,
        bool $autoConvert = true
    ) {
        $this->paramConverterManager = $paramConverterManager;
        $this->autoConvert = $autoConvert;
        $this->autoConfigurator = $autoConfigurator;
    }

    public function handleEvent(Event $event): void
    {
        if (!$event instanceof ParamConverterEvent) {
            return;
        }

        $context = $event->getContext();
        /** @var Request $request */
        $request = $context->getParameter(ParamConverterListenerContext::PARAM_REQUEST);

        $configurations = $this->extractConfigurations($request);

        $controller = $context->getParameter(ParamConverterListenerContext::PARAM_CONTROLLER);
        $method = $context->getParameter(ParamConverterListenerContext::PARAM_METHOD);

        // Automatically apply conversion for non-configured objects
        if ($this->autoConvert && is_callable([$controller, $method])) {
            $this->autoConfigurator->configure(
                new ReflectionMethod($controller, $method),
                $request,
                $configurations
            );
        }

        $this->paramConverterManager->apply($request, $configurations);
    }

    /**
     * @return ParamConverter[]
     */
    private function extractConfigurations(Request $request): array
    {
        $configurations = [];
        $requestConfigurations = $request->attributes->get(self::REQUEST_ATTRIBUTE_CONVERTERS, []);

        if (!is_array($requestConfigurations)) {
            $requestConfigurations = [$requestConfigurations];
        }

        /** @var ParamConverter $requestConfiguration */
        foreach ($requestConfigurations as $requestConfiguration) {
            $configurations[$requestConfiguration->getName()] = $requestConfiguration;
        }

        return $configurations;
    }
}
