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

use Throwable;
use oat\oatbox\filesystem\File;
use oat\oatbox\reporting\Report;
use core_kernel_classes_Resource;
use oat\tao\model\Csv\Factory\ReaderFactory;
use tao_models_classes_dataBinding_GenerisInstanceDataBinder;
use oat\tao\model\import\Processor\ImportFileProcessorInterface;
use oat\tao\model\StatisticalMetadata\Import\Result\ImportResult;
use oat\tao\model\StatisticalMetadata\Import\Builder\ReportBuilder;
use oat\tao\model\StatisticalMetadata\Import\Validator\HeaderValidator;
use oat\tao\model\StatisticalMetadata\Import\Extractor\ResourceExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataValuesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Exception\AbstractValidationException;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataPropertiesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;
use tao_models_classes_dataBinding_GenerisInstanceDataBindingException as DataBindingException;

// @TODO Improve tests - add invalid cases
class ImportProcessor implements ImportFileProcessorInterface
{
    /** @var ReaderFactory */
    private $readerFactory;

    /** @var HeaderValidator */
    private $headerValidator;

    /** @var MetadataPropertiesExtractor */
    private $metadataPropertiesExtractor;

    /** @var ResourceExtractor */
    private $resourceExtractor;

    /** @var MetadataValuesExtractor */
    private $metadataValuesExtractor;

    /** @var ReportBuilder */
    private $reportBuilder;

    /** @var tao_models_classes_dataBinding_GenerisInstanceDataBinder */
    private $dataBinder;

    /** @var NotifyImportService */
    private $notifyImportService;

    public function __construct(
        ReaderFactory $readerFactory,
        HeaderValidator $headerValidator,
        MetadataPropertiesExtractor $metadataPropertiesExtractor,
        ResourceExtractor $resourceExtractor,
        MetadataValuesExtractor $metadataValuesExtractor,
        ReportBuilder $reportBuilder,
        NotifyImportService $notifyImportService
    ) {
        $this->readerFactory = $readerFactory;
        $this->headerValidator = $headerValidator;
        $this->metadataPropertiesExtractor = $metadataPropertiesExtractor;
        $this->resourceExtractor = $resourceExtractor;
        $this->metadataValuesExtractor = $metadataValuesExtractor;
        $this->reportBuilder = $reportBuilder;
        $this->notifyImportService = $notifyImportService;
    }

    public function withDataBinder(tao_models_classes_dataBinding_GenerisInstanceDataBinder $dataBinder): void
    {
        $this->dataBinder = $dataBinder;
    }

    public function process(File $file): Report
    {
        $result = new ImportResult();

        try {
            $csv = $this->readerFactory->createFromStream(
                $file->readStream(),
                [ReaderFactory::DELIMITER => ';']
            );

            $header = $csv->getHeader();
            $this->headerValidator->validateRequiredHeaders($header);

            $metadataProperties = $this->metadataPropertiesExtractor->extract($header);
        } catch (AbstractValidationException | AggregatedValidationException $exception) {
            $result->addException(0, $exception);

            return $this->reportBuilder->buildByResult($result);
        } catch (Throwable $exception) {
            return $this->reportBuilder->buildByException($exception);
        }

        foreach ($csv->getRecords($header) as $line => $record) {
            try {
                $result->increaseTotalScannedRecords();
                $resource = $this->resourceExtractor->extract($record);
                $metadataValues = $this->metadataValuesExtractor->extract($record, $resource, $metadataProperties);

                $this->bindProperties($resource, $metadataValues);

                $result->addImportedRecord($record);

                $result->increaseTotalImportedRecords();
            } catch (AbstractValidationException | AggregatedValidationException $exception) {
                $result->addException($line, $exception);
            } catch (Throwable $exception) {
                return $this->reportBuilder->buildByException($exception);
            }
        }

        try {
            $this->notifyImportService->notify($result);
        } catch (Throwable $exception) {
            return $this->reportBuilder->buildByException($exception);
        }

        return $this->reportBuilder->buildByResult($result);
    }

    /**
     * @TODO Improve the repository to allow persistence of resource properties and use it instead of a data binder.
     *
     * @throws DataBindingException
     */
    private function bindProperties(core_kernel_classes_Resource $resource, array $values): void
    {
        $binder = $this->dataBinder ?? new tao_models_classes_dataBinding_GenerisInstanceDataBinder($resource);
        $binder->forceModification();
        $binder->bind($values);
    }
}
