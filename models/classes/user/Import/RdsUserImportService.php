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

namespace oat\tao\model\user\Import;

use oat\generis\Helper\UserHashForEncryption;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\user\UserRdf;
use oat\oatbox\event\EventManager;
use oat\oatbox\log\LoggerAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\event\UserUpdatedEvent;
use oat\tao\model\TaoOntology;
use common_report_Report as Report;

class RdsUserImportService extends ConfigurableService implements UserImportServiceInterface
{
    use LoggerAwareTrait;
    use OntologyAwareTrait;

    /** @var array Default CSV controls */
    protected $csvControls = [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
    ];

    /** @var array  */
    protected $headerColumns = [];

    /** @var UserMapper */
    private $mapper;

    /**
     * @param $filePath
     * @param array $extraProperties
     * @param array $options
     * @return Report
     * @throws \Exception
     * @throws \common_exception_Error
     */
    public function import($filePath, $extraProperties = [], $options = [])
    {
        $report = Report::createInfo('Starting importing users.');
        if (!file_exists($filePath) || !is_readable($filePath) || ($fileHandler = fopen($filePath, 'r')) === false) {
            throw new \Exception('File to import cannot be loaded.');
        }

        $csvControls = $this->getCsvControls($options);
        extract($csvControls);

        $index = 0;
        while (($line = fgetcsv($fileHandler, 0, $delimiter, $enclosure, $escape)) !== false) {
            $index++;
            $data = array_map('trim', $line);

            if (count($data) == 1) {
                $csvControlsString = implode(', ', array_map(
                    function ($v, $k) { return sprintf("%s: '%s'", $k, $v); },
                    $csvControls,
                    array_keys($csvControls)
                ));
                $report->add(Report::createFailure(
                    'It seems that the csv is malformed. The delimiter \'' . $delimiter . '\' does not explode the line correctly (only one cell).' .
                    "\n" . ' Csv controls are ' . $csvControlsString

                ));
                break;
            }

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

            try {
                $combinedRow = $this->formatData($data, $extraProperties);

                $mapper = $this->getUserMapper()->map($combinedRow)->combine($extraProperties);
                if ($mapper->isEmpty()) {
                    $message = 'Mapper doesn\'t achieve to extract data for line ' . $index . '. Data skipped';
                    $this->logWarning($message);
                    $report->add(Report::createFailure($message));
                    continue;
                }

                $user = $this->persistUser($mapper);
                $message = 'User imported with success: '. $user->getUri();
                $this->logInfo($message);
                $report->add(Report::createSuccess($message));
            } catch (\Exception $exception) {
                $report->add(Report::createFailure($exception->getMessage()));
            }
        }

        return $report;
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

    /**
     * Format the $data with $extraProperties
     *
     * @param array $data
     * @param array $extraProperties
     * @return array
     */
    protected function formatData(array $data, array $extraProperties)
    {
        $combinedRow = array_combine($this->headerColumns, $data);
        if (isset($extraProperties[UserRdf::PROPERTY_ROLES])) {
            $combinedRow['roles'] = $extraProperties[UserRdf::PROPERTY_ROLES];
        }
        return $combinedRow;
    }

    /**
     * Persist a user, create or update
     *
     * @param UserMapper $userMapper
     * @return \core_kernel_classes_Resource
     */
    protected function persistUser(UserMapper $userMapper)
    {
        $plainPassword = $userMapper->getPlainPassword();
        $properties    = $userMapper->getProperties();

        $class = $this->getUserClass($properties);

        $results = $class->searchInstances(
            [
                UserRdf::PROPERTY_LOGIN => $properties[UserRdf::PROPERTY_LOGIN]
            ],
            [
                'like' => false,
                'recursive' => true
            ]
        );

        if(count($results) > 0){
            $resource = $this->mergeUserProperties(current($results), $properties);
        } else {
            $resource = $class->createInstanceWithProperties($properties);
        }

        $this->triggerUserUpdated($resource, $properties, $plainPassword);

        return $resource;
    }

    /**
     * Get the user class
     *
     * @param array $properties
     * @return \core_kernel_classes_Class
     */
    protected function getUserClass(array $properties)
    {
        return $this->getClass(TaoOntology::CLASS_URI_TAO_USER);
    }

    /**
     * Trigger UserEvent at user update
     *
     * @param \core_kernel_classes_Resource $resource
     * @param array $properties
     * @param string $plainPassword
     */
    protected function triggerUserUpdated(\core_kernel_classes_Resource $resource, array $properties, $plainPassword)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->trigger(new UserUpdatedEvent($resource,
            array_merge(
                $properties,
                [
                    'hashForKey' => UserHashForEncryption::hash($plainPassword)
                ]
            )
        ));
    }

    /**
     * Flush rdf properties to a resource
     * - delete old
     * - insert new
     *
     * @param \core_kernel_classes_Resource $user
     * @param $properties
     * @return \core_kernel_classes_Resource
     */
    protected function mergeUserProperties(\core_kernel_classes_Resource $user, $properties)
    {
        foreach ($properties as $property => $value) {
            $user->removePropertyValues($this->getProperty($property));
            $user->editPropertyValues($this->getProperty($property), $value);
        }

        return $user;
    }

    /**
     * Get the user mapper to map csv column to rdf properties
     *
     * @return UserMapper
     */
    protected function getUserMapper()
    {
        if (is_null($this->mapper)) {
            throw new \LogicException('Mapper is not initialized and importer cannot process.');
        }

        return $this->mapper;
    }

    /**
     * Set the mapper
     *
     * @param UserMapper $userMapper
     * @return RdsUserImportService
     */
    public function setMapper(UserMapper $userMapper)
    {
        $this->mapper = $userMapper;
        return $this;
    }

}