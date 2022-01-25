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

use RuntimeException;
use InvalidArgumentException;
use oat\oatbox\filesystem\File;
use oat\oatbox\reporting\Report;
use core_kernel_classes_Resource;
use oat\tao\model\Csv\Factory\ReaderFactory;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use oat\tao\model\import\Processor\ImportFileProcessorInterface;
use oat\tao\model\StatisticalMetadata\Import\Validator\HeaderValidator;
use tao_models_classes_dataBinding_GenerisInstanceDataBindingException;
use oat\tao\model\StatisticalMetadata\Import\Extractor\ResourceExtractor;
use oat\tao\model\StatisticalMetadata\Import\Mapper\StatisticalMetadataMapper;
use oat\tao\model\StatisticalMetadata\Import\Validator\MetadataToBindValidator;

class ImportProcessor implements ImportFileProcessorInterface
{
    /** @var ReaderFactory */
    private $readerFactory;

    /** @var HeaderValidator */
    private $headerValidator;

    /** @var StatisticalMetadataMapper */
    private $statisticalMetadataMapper;

    /** @var ResourceExtractor */
    private $resourceExtractor;

    /** @var MetadataToBindValidator */
    private $metadataToBindValidator;

    /** @var Report[] */
    private $reports;

    public function __construct(
        ReaderFactory $readerFactory,
        HeaderValidator $headerValidator,
        StatisticalMetadataMapper $statisticalMetadataMapper,
        ResourceExtractor $resourceExtractor,
        MetadataToBindValidator $metadataToBindValidator
    ) {
        $this->readerFactory = $readerFactory;
        $this->headerValidator = $headerValidator;
        $this->statisticalMetadataMapper = $statisticalMetadataMapper;
        $this->resourceExtractor = $resourceExtractor;
        $this->metadataToBindValidator = $metadataToBindValidator;
    }

    public function process(File $file): Report
    {
        if (!$file->exists()) {
            return Report::createError('File not exists.');
        }

        try {
            $csv = $this->readerFactory->createFromStream($file->readStream());
            $csv->setDelimiter(';');
            $csv->setHeaderOffset(0);

            $header = $csv->getHeader();
            $this->headerValidator->validateRequiredHeaders($header);

            $metadataMap = $this->statisticalMetadataMapper->getMap($header);
            $this->headerValidator->validateMetadataHeaders($header, $metadataMap);
            $this->headerValidator->validateMetadataUniqueness($metadataMap);
            $this->headerValidator->validateMetadataTypes($metadataMap);
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return Report::createError(sprintf('CSV import failed: %s', $exception->getMessage()));
        }

        $this->reports = [];
        $updatedResourcesCount = 0;

        foreach ($csv->getRecords($header) as $line => $record) {
            try {
                $resource = $this->resourceExtractor->extract($record);

                $this->bindProperties(
                    $resource,
                    $this->getMetadataToBind($record, $metadataMap, $resource, $line)
                );

                ++$updatedResourcesCount;
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $this->reports[] = Report::createError(sprintf('Line %d: %s', $line, $exception->getMessage()));
            } catch (tao_models_classes_dataBinding_GenerisInstanceDataBindingException $exception) {
                $this->reports[] = Report::createError(
                    sprintf(
                        'Line %d: metadata values could not be persisted.',
                        $line
                    )
                );
            }
        }

        return $this->buildFinalReport($csv->count(), $updatedResourcesCount);
    }

    private function getMetadataToBind(
        array $record,
        array $metadataMap,
        core_kernel_classes_Resource $resource,
        int $line
    ): array {
        $metadataToBind = [];
        $reports = [];
        $possibleMetadataToBindCount = 0;
        $errors = 0;

        // Since the metadata map has been checked for uniqueness, $metadataProperties contains only one value
        foreach ($metadataMap as $metadata => $metadataProperties) {
            if (empty($record[$metadata])) {
                continue;
            }

            ++$possibleMetadataToBindCount;

            try {
                $this->metadataToBindValidator->validateRelationToResource(
                    $resource,
                    $metadataProperties[0][StatisticalMetadataMapper::KEY_DOMAIN]
                );
                $this->metadataToBindValidator->validateMetadataValue($record[$metadata]);
            } catch (InvalidArgumentException $exception) {
                $reports[] = Report::createWarning(
                    sprintf(
                        'Metadata "%s": %s',
                        $metadata,
                        $exception->getMessage()
                    )
                );
                ++$errors;

                continue;
            }

            $metadataToBind[$metadataProperties[0][StatisticalMetadataMapper::KEY_URI]] = $record[$metadata];
        }

        if ($errors) {
            $this->reports[] = $possibleMetadataToBindCount === $errors
                ? Report::createError(sprintf('Line %d: import failed', $line), null, $reports)
                : Report::createWarning(sprintf('Line %d: partially imported', $line), null, $reports);
        }

        return $metadataToBind;
    }

    /**
     * @throws tao_models_classes_dataBinding_GenerisInstanceDataBindingException
     */
    private function bindProperties(core_kernel_classes_Resource $resource, array $values): void
    {
        $binder = new tao_models_classes_dataBinding_GenerisInstanceDataBinder($resource);
        $binder->bind($values);
    }

    private function buildFinalReport(int $totalCount, int $updatedResourcesCount): Report
    {
        $type = Report::TYPE_SUCCESS;
        $template = 'CSV import successful: imported %d/%d row(s).';
        $templateData = [
            $updatedResourcesCount,
            $totalCount,
        ];

        if (!empty($this->reports)) {
            $type = $updatedResourcesCount === $totalCount
                ? Report::TYPE_ERROR
                : Report::TYPE_WARNING;
            $template = 'Imported %d/%d row(s)';

            [$warningsCount, $errorsCount] = $this->countReportTypes();

            if ($warningsCount) {
                $template .= '. %d warning(s)';
                $templateData[] = $warningsCount;
            }

            if ($errorsCount) {
                $template .= ', %d error(s)';
                $templateData[] = $errorsCount;
            }

            $template .= '.';
        }

        return new Report($type, vsprintf($template, $templateData), null, $this->reports);
    }

    private function countReportTypes(): array
    {
        $warningsCount = 0;
        $errorsCount = 0;

        foreach ($this->reports as $report) {
            $report->getType() === Report::TYPE_WARNING
                ? ++$warningsCount
                : ++$errorsCount;
        }

        return [$warningsCount, $errorsCount];
    }
}
