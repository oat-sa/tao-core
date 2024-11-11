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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\resources;

use oat\generis\model\data\Ontology;
use oat\oatbox\event\EventManager;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\resources\Service\ClassMover;
use oat\tao\model\resources\Service\InstanceCopier;
use oat\tao\model\resources\Service\ClassCopierProxy;
use oat\tao\model\resources\Service\ClassMetadataMapper;
use oat\tao\model\resources\Service\ClassMetadataCopier;
use oat\tao\model\resources\Service\InstanceCopierProxy;
use oat\tao\model\resources\Service\InstanceMetadataCopier;
use oat\tao\model\resources\Service\InstanceMover;
use oat\tao\model\resources\Service\ResourceTransferProxy;
use oat\tao\model\resources\Service\RootClassesListService;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\tao\model\resources\Specification\RootClassSpecification;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\TaoOntology;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

/**
 * @codeCoverageIgnore
 */
class CopierServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(ClassMetadataMapper::class, ClassMetadataMapper::class);

        $services
            ->set(ClassMetadataCopier::class, ClassMetadataCopier::class)
            ->share(false)
            ->args(
                [
                    service(ClassMetadataMapper::class),
                ]
            );

        $services
            ->set(InstanceMetadataCopier::class, InstanceMetadataCopier::class)
            ->share(false)
            ->args(
                [
                    service(ClassMetadataMapper::class),
                    service(FileReferenceSerializer::SERVICE_ID),
                    service(FileSystemService::SERVICE_ID),
                    [
                        TaoOntology::PROPERTY_LANGUAGE,
                        TaoOntology::PROPERTY_TRANSLATION_STATUS,
                        TaoOntology::PROPERTY_TRANSLATION_TYPE
                    ]
                ]
            );

        $services
            ->get(InstanceMetadataCopier::class)
            ->call(
                'addPropertyUrisToBlacklist',
                [
                    [
                        TaoOntology::PROPERTY_UNIQUE_IDENTIFIER,
                    ]
                ]
            );

        $services
            ->set(InstanceCopier::class, InstanceCopier::class)
            ->args(
                [
                    service(InstanceMetadataCopier::class),
                    service(Ontology::SERVICE_ID)
                ]
            );

        $services
            ->set(ClassCopierProxy::class, ClassCopierProxy::class)
            ->share(false)
            ->public()
            ->args(
                [
                    service(RootClassesListService::class),
                    service(Ontology::SERVICE_ID),
                ]
            );

        $services
            ->set(InstanceCopierProxy::class, InstanceCopierProxy::class)
            ->share(false)
            ->public()
            ->args(
                [
                    service(RootClassesListService::class),
                    service(Ontology::SERVICE_ID),
                ]
            );

        $services
            ->set(ClassMover::class, ClassMover::class)
            ->share(false)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(RootClassSpecification::class),
                    service(RootClassesListService::class),
                    service(EventManager::SERVICE_ID)
                ]
            )
            ->call(
                'withPermissionCopiers',
                [
                    tagged_iterator('tao.copier.permissions'),
                ]
            );

        $services
            ->set(InstanceMover::class, InstanceMover::class)
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(RootClassesListService::class),
                ]
            )
            ->call(
                'withPermissionCopiers',
                [
                    tagged_iterator('tao.copier.permissions'),
                ]
            );

        $services
            ->set(ResourceTransferProxy::class, ResourceTransferProxy::class)
            ->share(false)
            ->public()
            ->args(
                [
                    service(ClassCopierProxy::class),
                    service(InstanceCopierProxy::class),
                    service(ClassMover::class),
                    service(InstanceMover::class),
                    service(Ontology::SERVICE_ID),
                ]
            );
    }
}
