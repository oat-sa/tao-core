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

namespace oat\tao\model\Lists\ServiceProvider;

use oat\generis\model\data\Ontology;
use oat\generis\persistence\PersistenceManager;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\Lists\Business\Specification\PrimaryPropertySpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;
use oat\tao\model\Lists\Business\Specification\SecondaryPropertySpecification;
use oat\tao\model\Lists\Business\Validation\PropertyListValidator;
use oat\tao\model\Lists\Business\Validation\PropertyTypeValidator;
use oat\tao\model\Lists\DataAccess\Repository\DependencyRepository;
use oat\tao\model\Lists\Business\Validation\DependsOnPropertyValidator;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\Lists\Business\Specification\DependentPropertySpecification;
use oat\tao\model\Lists\DataAccess\Repository\DependentPropertiesRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ListsServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(DependentPropertySpecification::class, DependentPropertySpecification::class)
            ->public();

        $services
            ->set(DependencyRepository::class, DependencyRepository::class)
            ->public()
            ->args(
                [
                    service(PersistenceManager::SERVICE_ID),
                ]
            );

        $services
            ->set(DependsOnPropertyValidator::class, DependsOnPropertyValidator::class)
            ->public()
            ->args(
                [
                    service(DependencyRepository::class),
                    service(Ontology::SERVICE_ID),
                ]
            );

        $services
            ->set(PropertyTypeValidator::class, PropertyTypeValidator::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(PrimaryPropertySpecification::class),
                    service(SecondaryPropertySpecification::class),
                    service(FeatureFlagChecker::class),
                ]
            );

        $services
            ->set(PropertyListValidator::class, PropertyListValidator::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(PrimaryPropertySpecification::class),
                    service(SecondaryPropertySpecification::class),
                    service(RemoteListClassSpecification::class),
                    service(FeatureFlagChecker::class),
                ]
            );

        $services
            ->set(PrimaryPropertySpecification::class, PrimaryPropertySpecification::class)
            ->public()
            ->args(
                [
                    service(DependentPropertiesRepository::class),
                ]
            );

        $services
            ->set(SecondaryPropertySpecification::class, SecondaryPropertySpecification::class)
            ->public()
            ->args(
                [
                    service(DependentPropertySpecification::class),
                ]
            );
    }
}
