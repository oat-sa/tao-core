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

namespace oat\tao\model\StatisticalMetadata\Import\Processor;

use oat\oatbox\filesystem\File;
use oat\oatbox\reporting\Report;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use oat\tao\model\import\Processor\ImportFileProcessorInterface;
use oat\tao\model\StatisticalMetadata\Repository\StatisticalMetadataRepository;
use oat\tao\model\StatisticalMetadata\Contract\StatisticalMetadataRepositoryInterface;

class ImportStatisticalMetadataProcessor implements ImportFileProcessorInterface
{
    private const HEADER_METADATA_PREFIX = 'metadata_';

    /** @var StatisticalMetadataRepositoryInterface */
    private $statisticalMetadataRepository;

    /** @var Ontology */
    private $ontology;

    public function __construct(
        StatisticalMetadataRepositoryInterface $statisticalMetadataRepository,
        Ontology $ontology
    ) {
        $this->statisticalMetadataRepository = $statisticalMetadataRepository;
        $this->ontology = $ontology;
    }

    public function process(File $file): Report
    {
        $stream = $file->readStream();
        $metadataUris = $this->extractMetadataUris(fgetcsv($stream, 0, ';'));
        $metadataUrisCount = count($metadataUris);

        $totalCount = 0;
        $updatedResourcesCount = 0;

        while ($line = fgetcsv($stream, 0, ';')) {
            if ($line[0] === null) {
                continue;
            }

            ++$totalCount;

            $metadataValues = array_slice($line, 2);
            $resourceId = $line[0] ?: $line[1];

            if (count($metadataValues) !== $metadataUrisCount || empty($resourceId)) {
                continue;
            }

            $resource = $this->ontology->getResource($resourceId);

            if (!$resource->exists()) {
                continue;
            }

            $this->bindProperties($resource, array_combine($metadataUris, $metadataValues));
            ++$updatedResourcesCount;
        }

        return Report::createSuccess(
            sprintf(
                'Statistical analysis metadata import successful: %d/%d row%s imported.',
                $updatedResourcesCount,
                $totalCount,
                $updatedResourcesCount > 1 ? 's are' : ' is'
            )
        );
    }

    private function extractMetadataUris(array $line): array
    {
        $metadata = array_slice($line, 2);
        $this->replaceMetadataWithUris($metadata);

        return $metadata;
    }

    private function replaceMetadataWithUris(array &$metadata): void
    {
        foreach ($this->findMetadataProperties($metadata) as $metadataProperty) {
            $position = array_search(
                self::HEADER_METADATA_PREFIX . $metadataProperty->getAlias(),
                $metadata,
                true
            );
            $metadata[$position] = $metadataProperty->getUri();
        }
    }

    private function findMetadataProperties(array $metadata): array
    {
        $aliases = $this->extractMetadataAliases($metadata);

        return $this->statisticalMetadataRepository->findProperties(
            [
                StatisticalMetadataRepository::FILTER_ALIASES => $aliases,
            ]
        );
    }

    /**
     * @return string[]
     */
    private function extractMetadataAliases(array $metadata): array
    {
        return array_map(
            static function (string $metadataName): string {
                return str_replace(self::HEADER_METADATA_PREFIX, '', $metadataName);
            },
            $metadata
        );
    }

    private function bindProperties(core_kernel_classes_Resource $resource, array $values): void
    {
        $binder = new tao_models_classes_dataBinding_GenerisInstanceDataBinder($resource);
        $binder->bind($values);
    }
}
