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
 * Copyright (c) 2021 (update and modification) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\import\ServiceProvider;

use oat\generis\model\data\Ontology;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\import\Factory\ImportFormFactory;
use oat\tao\model\upload\UploadService;
use oat\tao\model\import\service\AgnosticImportHandler;
use Psr\Http\Message\ServerRequestInterface;
use Renderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use tao_models_classes_import_CsvImporter;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ImportServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(Renderer::class, Renderer::class)
            ->public();

        $services->set(tao_models_classes_import_CsvImporter::class, tao_models_classes_import_CsvImporter::class)
            ->public();

        $services->set(AgnosticImportHandler::class, AgnosticImportHandler::class)
            ->public()
            ->args(
                [
                    service(UploadService::SERVICE_ID),
                    service(Ontology::SERVICE_ID),
                ]
            );

        $services->set(ImportFormFactory::class, ImportFormFactory::class)
            ->public()
            ->args(
                [
                    service(ServerRequestInterface::class),
                    service(Ontology::SERVICE_ID),
                    service(Renderer::class),
                    service(Renderer::class),
                ]
            );
    }
}
