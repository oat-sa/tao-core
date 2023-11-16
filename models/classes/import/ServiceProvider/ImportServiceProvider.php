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
 * Copyright (c) 2021-2023 (update and modification) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\import\ServiceProvider;

use EasyRdf\Graph;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\generis\model\DependencyInjection\ServiceLink;
use oat\tao\model\import\CustomizedRdfImporter;
use oat\tao\model\import\service\AgnosticImportHandler;
use oat\tao\model\StatisticalMetadata\Import\Processor\ImportProcessor;
use oat\tao\model\upload\UploadService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ServiceLocator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ImportServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services
            ->set('upload_service.link', ServiceLink::class)
            ->args(
                [
                    UploadService::SERVICE_ID
                ]
            );

        $services
            ->set(AgnosticImportHandler::class, AgnosticImportHandler::class)
            ->public()
            ->args(
                [
                    service('upload_service.link'),
                ]
            );

        $services
            ->set(AgnosticImportHandler::STATISTICAL_METADATA_SERVICE_ID, AgnosticImportHandler::class)
            ->public()
            ->args(
                [
                    service('upload_service.link'),
                ]
            )
            ->call(
                'withFileProcessor',
                [
                    service(ImportProcessor::class),
                ]
            )
            ->call(
                'withLabel',
                [
                    __('CSV file'),
                ]
            );

        $services
            ->set(CustomizedRdfImporter::class, CustomizedRdfImporter::class)
            ->args(
                [
                    inline_service(Graph::class),
                ]
            )
            ->public();
    }
}
