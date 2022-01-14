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

namespace oat\tao\model\StatisticalMetadata\Import;

use RuntimeException;
use League\Csv\Reader;
use League\Csv\Exception;
use InvalidArgumentException;
use oat\tao\model\TaoOntology;
use core_kernel_classes_Class;
use oat\oatbox\filesystem\File;
use oat\oatbox\reporting\Report;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use oat\tao\model\import\Processor\ImportFileProcessorInterface;
use oat\tao\model\StatisticalMetadata\Repository\StatisticalMetadataRepository;
use oat\tao\model\StatisticalMetadata\Contract\StatisticalMetadataRepositoryInterface;

/**
 * @TODO Extract \League\Csv\Reader to a separate service
 * @TODO Extract validation to a separate service
 * @TODO Extract reporting to a separate service
 */
class ImportStatisticalMetadataProcessor implements ImportFileProcessorInterface
{
    private const HEADER_METADATA_PREFIX = 'metadata_';
    private const HEADER_ITEM_ID = 'itemId';
    private const HEADER_TEST_ID = 'testId';

    /** @var StatisticalMetadataRepositoryInterface */
    private $statisticalMetadataRepository;

    /** @var Ontology */
    private $ontology;

    /** @var core_kernel_classes_Class */
    private $itemRootClass;

    /** @var core_kernel_classes_Class */
    private $testRootClass;

    public function __construct(
        StatisticalMetadataRepositoryInterface $statisticalMetadataRepository,
        Ontology $ontology
    ) {
        $this->statisticalMetadataRepository = $statisticalMetadataRepository;
        $this->ontology = $ontology;

        $this->itemRootClass = $ontology->getClass(TaoOntology::CLASS_URI_ITEM);
        $this->testRootClass = $ontology->getClass(TaoOntology::CLASS_URI_TEST);
    }

    public function process(File $file): Report
    {
        if (!$file->exists()) {
            return Report::createError('File not exists.');
        }

        try {
            $csv = Reader::createFromStream($file->readStream());
            $csv->setDelimiter(';');
            $csv->setHeaderOffset(0);
        } catch (Exception $exception) {
            return Report::createError($exception->getMessage());
        }

        $header = $csv->getHeader();
        $metadataMap = $this->getMetadataMap($header);

        $updatedResourcesCount = 0;
        $reports = [];

        foreach ($csv->getRecords($header) as $line => $record) {
            try {
                $resourceHeader = $this->extractResourceHeader($record, $line);
                $resource = $this->getResource($record[$resourceHeader], $line);
                $this->validateResourceType($resource, $resourceHeader, $line);
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $reports[] = Report::createWarning($exception->getMessage());

                continue;
            }

            $this->bindProperties(
                $resource,
                $this->getMetadataToBind($record, $metadataMap, $resource)
            );
            ++$updatedResourcesCount;
        }

        return $this->buildFinalReport($csv->count(), $updatedResourcesCount, $reports);
    }

    private function getMetadataMap(array $header): array
    {
        $metadataProperties = $this->statisticalMetadataRepository->findProperties(
            [
                StatisticalMetadataRepository::FILTER_ALIASES => $this->extractMetadataAliases($header),
            ]
        );

        $map = [];

        foreach ($metadataProperties as $metadataProperty) {
            $map[self::HEADER_METADATA_PREFIX . $metadataProperty->alias][] = [
                'uri' => $metadataProperty->uri,
                'domain' => $this->ontology->getClass($metadataProperty->domain),
            ];
        }

        return $map;
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

    private function extractResourceHeader(array $record, int $line): string
    {
        if (!empty($record[self::HEADER_ITEM_ID])) {
            return self::HEADER_ITEM_ID;
        }

        if (!empty($record[self::HEADER_TEST_ID])) {
            return self::HEADER_TEST_ID;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Line %d: resource ID (header: %s or %s) not specified.',
                $line,
                self::HEADER_ITEM_ID,
                self::HEADER_TEST_ID
            )
        );
    }

    private function getResource(string $resourceId, int $line): core_kernel_classes_Resource
    {
        $resource = $this->ontology->getResource($resourceId);

        if (!$resource->exists()) {
            throw new RuntimeException(
                sprintf(
                    'Line %d: resource with ID "%s" does not exist.',
                    $line,
                    $resourceId
                )
            );
        }

        return $resource;
    }

    private function validateResourceType(
        core_kernel_classes_Resource $resource,
        string $resourceHeader,
        int $line
    ): void {
        $resourceRootClass = $resourceHeader === self::HEADER_ITEM_ID
            ? $this->itemRootClass
            : $this->testRootClass;

        if (!$resource->isInstanceOf($resourceRootClass)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Line %d: resource with ID "%s" is not valid, has the wrong instance type.',
                    $line,
                    $resource->getUri()
                )
            );
        }
    }

    private function getMetadataToBind(array $record, array $metadataMap, core_kernel_classes_Resource $resource): array
    {
        $metadataToBind = [];

        foreach ($metadataMap as $metadata => $metadataProperties) {
            if (empty($record[$metadata])) {
                continue;
            }

            foreach ($metadataProperties as $metadataProperty) {
                if ($resource->isInstanceOf($metadataProperty['domain'])) {
                    $metadataToBind[$metadataProperty['uri']] = $record[$metadata];

                    break;
                }
            }
        }

        return $metadataToBind;
    }

    private function bindProperties(core_kernel_classes_Resource $resource, array $values): void
    {
        $binder = new tao_models_classes_dataBinding_GenerisInstanceDataBinder($resource);
        $binder->bind($values);
    }

    private function buildFinalReport(int $totalCount, int $updatedResourcesCount, array $reports): Report
    {
        $errorsCount = $totalCount - $updatedResourcesCount;

        if ($errorsCount) {
            $report = $errorsCount === $totalCount
                ? $this->createErrorReport($totalCount, $updatedResourcesCount, $errorsCount)
                : $this->createWarningReport($totalCount, $updatedResourcesCount, $errorsCount);

            return $report->add($reports);
        }

        return $this->createSuccessReport($totalCount, $updatedResourcesCount);
    }

    private function createErrorReport(int $totalCount, int $updatedResourcesCount, int $errorsCount): Report
    {
        return Report::createError(
            sprintf(
                'Imported %d/%d %s. %d errors.',
                $updatedResourcesCount,
                $totalCount,
                $totalCount > 1 ? 'rows' : 'row',
                $errorsCount
            )
        );
    }

    private function createWarningReport(int $totalCount, int $updatedResourcesCount, int $errorsCount): Report
    {
        return Report::createWarning(
            sprintf(
                'Imported %d/%d %s. %d errors.',
                $updatedResourcesCount,
                $totalCount,
                $totalCount > 1 ? 'rows' : 'row',
                $errorsCount
            )
        );
    }

    private function createSuccessReport(int $totalCount, int $updatedResourcesCount): Report
    {
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
