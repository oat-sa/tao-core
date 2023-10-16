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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\search\ServiceProvider;

use oat\generis\model\data\Ontology;
use oat\generis\model\data\permission\PermissionInterface;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\Lists\Business\Service\ValueCollectionService;
use oat\tao\model\Lists\Business\Specification\RemoteListPropertySpecification;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilder;
use oat\tao\model\search\index\DocumentBuilder\IndexDocumentBuilderInterface;
use oat\tao\model\search\index\DocumentBuilder\PropertyIndexReferenceFactory;
use oat\tao\model\search\SearchTokenGenerator;
use oat\tao\model\search\Service\DefaultSearchSettingsService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @codeCoverageIgnore
 */
class SearchServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(DefaultSearchSettingsService::class, DefaultSearchSettingsService::class)
            ->public();

        $services->set(PropertyIndexReferenceFactory::class, PropertyIndexReferenceFactory::class)
            ->public();

        $services->set(IndexDocumentBuilderInterface::class, IndexDocumentBuilder::class)
            ->public()
            ->args(
                [
                    service(Ontology::SERVICE_ID),
                    service(SearchTokenGenerator::class),
                    service(PropertyIndexReferenceFactory::class),
                    service(ValueCollectionService::class),
                    service(RemoteListPropertySpecification::class),
                    service(PermissionInterface::SERVICE_ID),
                ]
            );
    }
}
