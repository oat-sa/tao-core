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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\import\service;

use oat\oatbox\filesystem\File;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use common_report_Report as Report;

abstract class AbstractImportService extends ConfigurableService implements ImportServiceInterface
{
    use LoggerAwareTrait;

    /** @var array Default CSV controls */
    protected $csvControls = [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
    ];

    /** @var array  */
    protected $headerColumns = [];

    /** @var ImportMapperInterface */
    protected $mapper;

    /**
     * @param ImportMapperInterface $mapper
     * @return \core_kernel_classes_Resource
     */
    abstract protected function persist(ImportMapperInterface $mapper);

    /**
     * @param $file
     * @param array $extraProperties
     * @param array $options
     * @return Report
     * @throws \Exception
     * @throws \common_exception_Error
     */
    public function import($file, $extraProperties = [], $options = [])
    {
        $report = \common_report_Report::createInfo();

        if ($file instanceof File){
            if ($file->exists()){
                $fileHandler = $file->readStream();
            }else{
                throw new \Exception('File to import cannot be loaded.');
            }
        } else if (!file_exists($file) || !is_readable($file) || ($fileHandler = fopen($file, 'r')) === false) {
            throw new \Exception('File to import cannot be loaded.');
        }

        $csvControls = $this->getCsvControls($options);
        list($delimiter, $enclosure, $escape) = array_values($csvControls);
        $index = 0;
        while (($line = fgetcsv($fileHandler, 0, $delimiter, $enclosure, $escape)) !== false) {
            $index++;
            $data = array_map('trim', $line);
            try {
                if ($index === 1) {
                    $this->headerColumns = array_map('strtolower', $data);
                    continue;
                }

                if (count($this->headerColumns) !== count($data)) {
                    $message = 'CSV file is malformed at line ' . $index . '. Data skipped';
                    $this->logWarning($message);
                    $report->add(Report::createFailure($message));
                    continue;
                }

                $combinedRow = array_combine($this->headerColumns, $data);
                $combinedRow = array_merge($combinedRow, $extraProperties);

                $mapper = $this->getMapper()->map($combinedRow)->combine($extraProperties);
                $report->add($mapper->getReport());

                if ($mapper->isEmpty()) {
                    $message = 'Mapper doesn\'t achieve to extract data for line ' . $index . '. Data skipped';
                    $this->logWarning($message);
                    $report->add(Report::createFailure($message));
                    continue;
                }

                $resource = $this->persist($mapper);
                $message = 'Resource imported with success: '. $resource->getUri();
                $this->logInfo($message);
                $report->add(Report::createSuccess($message));
            } catch (\Exception $exception) {
                $report->add(Report::createFailure($exception->getMessage()));
            }
        }

        if ($report->containsError()){
            $report->setMessage(__('Import failed.'));
            $report->setType(Report::TYPE_ERROR);
        }else {
            $report->setMessage(__('Import succeeded.'));
            $report->setType(Report::TYPE_SUCCESS);
        }

        return $report;
    }

    /**
     * Get the mapper
     *
     * @return ImportMapperInterface
     */
    public function getMapper()
    {
        if (is_null($this->mapper)) {
            throw new \LogicException('Mapper is not initialized and importer cannot process.');
        }

        return $this->mapper;
    }

    /**
     * Set the mapper
     *
     * @param ImportMapperInterface $mapper
     * @return $this
     */
    public function setMapper(ImportMapperInterface $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * Merge the given $options csv controls to default
     *
     * @param array $options
     * @return array
     */
    protected function getCsvControls(array $options)
    {
        $csvControls = $this->csvControls;
        if (isset($options['delimiter'])) {
            $csvControls['delimiter'] = $options['delimiter'];
        }
        if (isset($options['enclosure'])) {
            $csvControls['enclosure'] = $options['enclosure'];
        }
        if (isset($options['escape'])) {
            $csvControls['escape'] = $options['escape'];
        }
        return $csvControls;
    }
}
