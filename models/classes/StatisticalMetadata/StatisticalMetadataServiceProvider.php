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
 */

declare(strict_types=1);

namespace oat\tao\model\StatisticalMetadata;

use oat\generis\model\data\Ontology;
use oat\tao\model\Csv\Factory\ReaderFactory;
use oat\generis\model\resource\Repository\PropertyRepository;
use oat\tao\model\StatisticalMetadata\Import\Builder\ReportBuilder;
use oat\tao\model\StatisticalMetadata\Import\Processor\ImportProcessor;
use oat\tao\model\StatisticalMetadata\Import\Validator\HeaderValidator;
use oat\tao\model\StatisticalMetadata\Import\Extractor\ResourceExtractor;
use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataValuesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Validator\RecordResourceValidator;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataAliasesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataHeadersExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataPropertiesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Validator\MetadataPropertiesValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use oat\tao\model\StatisticalMetadata\Import\Validator\ResourceMetadataRelationValidator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class StatisticalMetadataServiceProvider implements ContainerServiceProviderInterface
{
    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->set(MetadataHeadersExtractor::class, MetadataHeadersExtractor::class);

        $services
            ->set(MetadataAliasesExtractor::class, MetadataAliasesExtractor::class)
            ->args(
                [
                    service(MetadataHeadersExtractor::class),
                ]
            );

        $services
            ->set(HeaderValidator::class, HeaderValidator::class)
            ->args(
                [
                    service(MetadataHeadersExtractor::class),
                ]
            );

        $services->set(MetadataPropertiesValidator::class, MetadataPropertiesValidator::class);

        $services
            ->set(MetadataPropertiesExtractor::class, MetadataPropertiesExtractor::class)
            ->args(
                [
                    service(MetadataAliasesExtractor::class),
                    service(PropertyRepository::class),
                    service(MetadataPropertiesValidator::class),
                ]
            );

        $services->set(RecordResourceValidator::class, RecordResourceValidator::class);

        $services
            ->set(ResourceExtractor::class, ResourceExtractor::class)
            ->args(
                [
                    service(RecordResourceValidator::class),
                    service(Ontology::SERVICE_ID),
                ]
            );

        $services->set(ResourceMetadataRelationValidator::class, ResourceMetadataRelationValidator::class);

        $services
            ->set(MetadataValuesExtractor::class, MetadataValuesExtractor::class)
            ->args(
                [
                    service(MetadataHeadersExtractor::class),
                    service(ResourceMetadataRelationValidator::class),
                ]
            );

        $services->set(ReportBuilder::class, ReportBuilder::class);

        $services
            ->set(ImportProcessor::class, ImportProcessor::class)
            ->args(
                [
                    service(ReaderFactory::class),
                    service(HeaderValidator::class),
                    service(MetadataPropertiesExtractor::class),
                    service(ResourceExtractor::class),
                    service(MetadataValuesExtractor::class),
                    service(ReportBuilder::class),
                ]
            );
    }
}
