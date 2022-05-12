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
 * Copyright (c) 2021-2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\resources;

use oat\generis\model\data\Ontology;
use oat\tao\model\resources\Service\ClassCopier;
use oat\tao\model\resources\Service\ClassDeleter;
use oat\tao\model\accessControl\PermissionChecker;
use oat\tao\model\resources\Service\InstanceCopier;
use oat\tao\model\resources\Service\ClassCopierProxy;
use oat\tao\model\resources\Service\ClassMetadataCopier;
use oat\generis\model\resource\Repository\ClassRepository;
use oat\tao\model\resources\Service\RootClassesListService;
use oat\tao\model\resources\Service\InstanceMetadataCopier;
use oat\generis\model\resource\Repository\ResourceRepository;
use oat\tao\model\resources\Specification\RootClassSpecification;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ResourcesServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set(RootClassesListService::class, RootClassesListService::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                ]
            );

        $services
            ->set(RootClassSpecification::class, RootClassSpecification::class)
            ->args(
                [
                    service(RootClassesListService::class),
                ]
            );

        $services
            ->set(ClassDeleter::class, ClassDeleter::class)
            ->public()
            ->args(
                [
                    service(RootClassSpecification::class),
                    service(PermissionChecker::class),
                    service(Ontology::SERVICE_ID),
                    service(ResourceRepository::class),
                    service(ClassRepository::class),
                ]
            );

        $services->set(ClassMetadataCopier::class, ClassMetadataCopier::class);

        $services->set(InstanceMetadataCopier::class, InstanceMetadataCopier::class);

        $services
            ->set(InstanceCopier::class, InstanceCopier::class)
            ->args(
                [
                    service(InstanceMetadataCopier::class),
                ]
            );

        $services
            ->set(ClassCopierProxy::class, ClassCopierProxy::class)
            ->share(false)
            ->public()
            ->args(
                [
                    service(RootClassesListService::class),
                ]
            );
    }
}
