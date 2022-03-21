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

namespace oat\tao\model\ParamConverter;

use oat\tao\model\Serializer\Serializer;
use oat\tao\model\ParamConverter\Factory\ObjectFactory;
use oat\tao\model\ParamConverter\Configuration\Configurator;
use oat\tao\model\ParamConverter\Request\QueryParamConverter;
use oat\tao\model\ParamConverter\Manager\ParamConverterManager;
use oat\tao\model\ParamConverter\EventListener\ParamConverterListener;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ParamConverterServiceProvider implements ContainerServiceProviderInterface
{
    private const PARAM_CONVERTERS = [
        'oat.tao.param_converter.query' => QueryParamConverter::class,
    ];

    /** @var ServicesConfigurator */
    private $services;

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $this->services = $configurator->services();

        $this->provideConverters();
        $this->provideParamConverterManager();
        $this->provideParamConverterListener();
    }

    private function provideConverters(): void
    {
        $this->services
            ->set(ObjectFactory::class, ObjectFactory::class)
            ->args(
                [
                    service(Serializer::class),
                ]
            );

        foreach (self::PARAM_CONVERTERS as $paramConverter) {
            $this->services
                ->set($paramConverter, $paramConverter)
                ->args(
                    [
                        service(ObjectFactory::class),
                    ]
                );
        }
    }

    private function provideParamConverterManager(): void
    {
        $this->services->set(ParamConverterManager::class, ParamConverterManager::class);
        $paramConverterManager = $this->services->get(ParamConverterManager::class);

        foreach (self::PARAM_CONVERTERS as $name => $paramConverterId) {
            $paramConverterManager->call(
                'add',
                [
                    service($paramConverterId),
                    $name,
                ]
            );
        }
    }

    private function provideParamConverterListener(): void
    {
        $this->services->set(Configurator::class, Configurator::class);

        $this->services
            ->set(ParamConverterListener::class, ParamConverterListener::class)
            ->public()
            ->args(
                [
                    service(Configurator::class),
                    service(ParamConverterManager::class),
                    true,
                ]
            );
    }
}
