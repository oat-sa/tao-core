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

namespace oat\tao\model\export\ServiceProvider;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\export\Metadata\JsonLd\JsonLdBasicTripleEncoder;
use oat\tao\model\export\Metadata\JsonLd\JsonLdListTripleEncoder;
use oat\tao\model\export\Metadata\JsonLd\JsonLdTripleEncoderProxy;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Business\Specification\LocalListClassSpecification;
use oat\tao\model\Lists\Business\Specification\RemoteListClassSpecification;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class MetadataServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(JsonLdBasicTripleEncoder::class, JsonLdBasicTripleEncoder::class)
            ->public();

        $services->set(JsonLdListTripleEncoder::class, JsonLdListTripleEncoder::class)
            ->public()
            ->args(
                [
                    service(ValueCollectionService::SERVICE_ID),
                    service(RemoteListClassSpecification::class),
                    service(LocalListClassSpecification::class),
                ]
            );

        $services->set(JsonLdTripleEncoderProxy::class, JsonLdTripleEncoderProxy::class)
            ->public()
            ->call(
                'addEncoder',
                [
                    service(JsonLdListTripleEncoder::class),
                ]
            )
            ->call(
                'addEncoder',
                [
                    service(JsonLdBasicTripleEncoder::class),
                ]
            )
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                ]
            );
    }
}
