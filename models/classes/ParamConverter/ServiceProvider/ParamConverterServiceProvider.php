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

namespace oat\tao\model\ParamConverter\ServiceProvider;

use oat\tao\model\ParamConverter\Configuration\Configurator;
use oat\tao\model\ParamConverter\Request\QueryParamConverter;
use oat\tao\model\ParamConverter\Manager\ParamConverterManager;
use oat\tao\model\ParamConverter\EventListener\ParamConverterListener;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ParametersConfigurator;

class ParamConverterServiceProvider implements ContainerServiceProviderInterface
{
    private const PARAM_CONVERTERS = [
        QueryParamConverter::class,
    ];

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $this->provideConverters($services);
        $this->provideParamConverterManager($services);
        $this->provideParamConverterListener($services, $parameters);
    }

    private function provideConverters(ServicesConfigurator $services): void
    {
        foreach (self::PARAM_CONVERTERS as $paramConverter) {
            $services
                ->set($paramConverter, $paramConverter)
                ->public();
        }
    }

    private function provideParamConverterManager(ServicesConfigurator $services): void
    {
        $services
            ->set(ParamConverterManager::class, ParamConverterManager::class)
            ->public()
            ->args(
                [
                    array_map(
                        static function (string $paramConverter) {
                            return service($paramConverter);
                        },
                        self::PARAM_CONVERTERS
                    ),
                ]
            );
    }

    private function provideParamConverterListener(
        ServicesConfigurator $services,
        ParametersConfigurator $parameters
    ): void {
        $services
            ->set(Configurator::class, Configurator::class)
            ->public();

        $parameters->set('autoConvert', true);
        $services
            ->set(ParamConverterListener::class, ParamConverterListener::class)
            ->public()
            ->args(
                [
                    service(Configurator::class),
                    service(ParamConverterManager::class),
                    param('autoConvert'),
                ]
            );
    }
}
