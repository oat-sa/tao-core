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

namespace oat\tao\helpers\form\ServiceProvider;

use oat\tao\helpers\form\Factory\ElementPropertyEmptyListValuesFactory;
use oat\tao\helpers\form\Factory\ElementPropertyListValuesFactory;
use oat\tao\helpers\form\Factory\ElementPropertyTypeFactory;
use oat\tao\helpers\form\Specification\DependencyPropertyWidgetSpecification;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\Lists\Business\Specification\PrimaryPropertySpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class FormServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(ElementPropertyTypeFactory::class, ElementPropertyTypeFactory::class)
            ->public()
            ->args(
                [
                    service(PrimaryPropertySpecification::class),
                    service(SecondaryPropertySpecification::class),
                    service(DependencyPropertyWidgetSpecification::class),
                    service(FeatureFlagChecker::class),
                ]
            );

        $services
            ->set(ElementPropertyEmptyListValuesFactory::class, ElementPropertyEmptyListValuesFactory::class)
            ->public()
            ->args(
                [
                    service(PrimaryPropertySpecification::class),
                    service(SecondaryPropertySpecification::class),
                    service(FeatureFlagChecker::class),
                ]
            );

        $services
            ->set(ElementPropertyListValuesFactory::class, ElementPropertyListValuesFactory::class)
            ->public()
            ->args(
                [
                    service(RemoteListClassSpecification::class)
                ]
            );

        $services
            ->set(DependencyPropertyWidgetSpecification::class, DependencyPropertyWidgetSpecification::class);
    }
}
