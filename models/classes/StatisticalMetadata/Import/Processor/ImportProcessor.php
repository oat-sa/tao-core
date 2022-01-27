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
use oat\tao\model\StatisticalMetadata\Import\Builder\ReportBuilder;
use oat\tao\model\StatisticalMetadata\Import\Reporter\ImportReporter;
use oat\tao\model\StatisticalMetadata\Import\Validator\HeaderValidator;
use oat\tao\model\StatisticalMetadata\Import\Extractor\ResourceExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataValuesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Extractor\MetadataPropertiesExtractor;
use oat\tao\model\StatisticalMetadata\Import\Exception\AbstractValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;
use tao_models_classes_dataBinding_GenerisInstanceDataBindingException as DataBindingException;

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

    public function __construct(
        ReaderFactory $readerFactory,
        HeaderValidator $headerValidator,
        MetadataPropertiesExtractor $metadataPropertiesExtractor,
        ResourceExtractor $resourceExtractor,
        MetadataValuesExtractor $metadataValuesExtractor,
        ReportBuilder $reportBuilder
    ) {
        $this->readerFactory = $readerFactory;
        $this->headerValidator = $headerValidator;
        $this->metadataPropertiesExtractor = $metadataPropertiesExtractor;
        $this->resourceExtractor = $resourceExtractor;
        $this->metadataValuesExtractor = $metadataValuesExtractor;
        $this->reportBuilder = $reportBuilder;
    }

    public function process(File $file): Report
    {
        $reporter = new ImportReporter();

        try {
            $csv = $this->readerFactory->createFromStream(
                $file->readStream(),
                [ReaderFactory::DELIMITER => ';']
            );

            $header = $csv->getHeader();
            $this->headerValidator->validateRequiredHeaders($header);

            $metadataProperties = $this->metadataPropertiesExtractor->extract($header);
        } catch (AbstractValidationException | AggregatedValidationException $exception) {
            // @TODO Beautify header exceptions
            $reporter->addException(0, $exception);

            return $this->reportBuilder->buildByReporter($reporter);
        } catch (Throwable $exception) {
            return $this->reportBuilder->buildByException($exception);
        }

        foreach ($csv->getRecords($header) as $line => $record) {
            try {
                $reporter->increaseTotalScannedRecords();
                $resource = $this->resourceExtractor->extract($record);
                $metadataValues = $this->metadataValuesExtractor->extract($record, $resource, $metadataProperties);

                $this->bindProperties($resource, $metadataValues);

                $reporter->increaseTotalImportedRecords();
            } catch (AbstractValidationException | AggregatedValidationException $exception) {
                $reporter->addException($line, $exception);
            } catch (Throwable $exception) {
                return $this->reportBuilder->buildByException($exception);
            }
        }

        return $this->reportBuilder->buildByReporter($reporter);
    }

    /**
     * @TODO Improve the repository to allow persistence of resource properties and use it instead of a data binder.
     *
     * @throws DataBindingException
     */
    private function bindProperties(core_kernel_classes_Resource $resource, array $values): void
    {
        $binder = new tao_models_classes_dataBinding_GenerisInstanceDataBinder($resource);
        $binder->bind($values);
    }
}
