<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\EventListener;

use ReflectionMethod;
use oat\tao\model\ParamConverter\Event\Event;
use oat\tao\model\HttpFoundation\Request\RequestInterface;
use oat\tao\model\ParamConverter\Event\ParamConverterEvent;
use oat\tao\model\ParamConverter\Configuration\ParamConverter;
use oat\tao\model\ParamConverter\Configuration\ConfiguratorInterface;
use oat\tao\model\ParamConverter\Manager\ParamConverterManagerInterface;

class ParamConverterListener implements ListenerInterface
{
    public const REQUEST_ATTRIBUTE_CONVERTERS = '_converters';

    /** @var ConfiguratorInterface */
    private $configurator;

    /** @var ParamConverterManagerInterface */
    private $paramConverterManager;

    /** @var bool */
    private $autoConvert;

    public function __construct(
        ConfiguratorInterface $autoConfigurator,
        ParamConverterManagerInterface $paramConverterManager,
        bool $autoConvert = true
    ) {
        $this->configurator = $autoConfigurator;
        $this->paramConverterManager = $paramConverterManager;
        $this->autoConvert = $autoConvert;
    }

    public function handleEvent(Event $event): void
    {
        if (!$event instanceof ParamConverterEvent) {
            return;
        }

        $context = $event->getContext();
        $request = $context->getRequest();

        $configurations = $this->extractConfigurations($request);

        $controller = $context->getController();
        $method = $context->getMethod();

        // Automatically apply conversion for non-configured objects
        if ($this->autoConvert && is_callable([$controller, $method])) {
            $this->configurator->configure(
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
    private function extractConfigurations(RequestInterface $request): array
    {
        $configurations = [];
        $requestConfigurations = $request->getAttribute(self::REQUEST_ATTRIBUTE_CONVERTERS, []);

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
