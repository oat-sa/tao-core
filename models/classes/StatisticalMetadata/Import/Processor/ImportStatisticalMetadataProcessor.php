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

use League\Csv\Reader;
use League\Csv\Exception;
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
    private const HEADER_ITEM_ID = 'itemId';
    private const HEADER_TEST_ID = 'testId';

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

    // @TODO Extract validation to a separate service
    public function process(File $file): Report
    {
        if (!$file->exists()) {
            return Report::createError('File not exists.');
        }

        try {
            // @TODO Extract \League\Csv\Reader to a separate service
            $csv = Reader::createFromStream($file->readStream());
            $csv->setDelimiter(';');
            $csv->setHeaderOffset(0);
        } catch (Exception $exception) {
            return Report::createError($exception->getMessage());
        }

        $header = $csv->getHeader();
        $header = $this->replaceHeaderMetadataWithUris($header);

        $updatedResourcesCount = 0;
        $reports = [];

        foreach ($csv->getRecords($header) as $line => $record) {
            $resourceId = $record[self::HEADER_ITEM_ID] ?: $record[self::HEADER_TEST_ID] ?: null;

            if ($resourceId === null) {
                $reports[] = Report::createWarning(
                    sprintf(
                        'Resource ID (%s or %s) at line %d was not provided.',
                        self::HEADER_ITEM_ID,
                        self::HEADER_TEST_ID,
                        $line
                    )
                );

                continue;
            }

            $resource = $this->ontology->getResource($resourceId);

            if (!$resource->exists()) {
                $reports[] = Report::createWarning(
                    sprintf(
                        'Resource with ID "%s" at line %d not exists.',
                        $resourceId,
                        $line
                    )
                );

                continue;
            }

            unset($record[self::HEADER_ITEM_ID], $record[self::HEADER_TEST_ID]);

            $this->bindProperties($resource, $record);
            ++$updatedResourcesCount;
        }

        return $this->buildFinalReport($csv->count(), $updatedResourcesCount, $reports);
    }

    private function replaceHeaderMetadataWithUris(array $header): array
    {
        foreach ($this->findMetadataProperties($header) as $metadataProperty) {
            $position = array_search(
                self::HEADER_METADATA_PREFIX . $metadataProperty->getAlias(),
                $header,
                true
            );
            $header[$position] = $metadataProperty->getUri();
        }

        return $header;
    }

    private function findMetadataProperties(array $header): array
    {
        $aliases = $this->extractMetadataAliases($header);

        return $this->statisticalMetadataRepository->findProperties(
            [
                StatisticalMetadataRepository::FILTER_ALIASES => $aliases,
            ]
        );
    }

    private function extractMetadataAliases(array $header): array
    {
        $metadataAliases = [];

        foreach ($header as $name) {
            if (strpos($name, self::HEADER_METADATA_PREFIX) === 0) {
                $metadataAliases[] = str_replace(self::HEADER_METADATA_PREFIX, '', $name);
            }
        }

        return $metadataAliases;
    }

    private function bindProperties(core_kernel_classes_Resource $resource, array $values): void
    {
        $binder = new tao_models_classes_dataBinding_GenerisInstanceDataBinder($resource);
        $binder->bind($values);
    }

    // @TODO Extract reporting to a separate service
    private function buildFinalReport(int $totalCount, int $updatedResourcesCount, array $reports): Report
    {
        $errorsCount = $totalCount - $updatedResourcesCount;

        if ($errorsCount) {
            $message = sprintf(
                'Imported %d/%d %s. %d errors.',
                $updatedResourcesCount,
                $totalCount,
                $totalCount > 1 ? 'rows' : 'row',
                $errorsCount
            );

            $report = $errorsCount === $totalCount
                ? Report::createError($message)
                : Report::createWarning($message);

            return $report->add($reports);
        }

        return Report::createSuccess(
            sprintf(
                'Statistical analysis metadata import successful: imported %d/%d %s.',
                $updatedResourcesCount,
                $totalCount,
                $totalCount > 1 ? 'rows' : 'row'
            )
        );
    }
}
