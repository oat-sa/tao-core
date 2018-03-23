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
use oat\generis\model\OntologyRdf;
use oat\generis\model\user\UserRdf;
use oat\oatbox\event\Event;
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

    protected $options = [
        'delimiter' => ',',
        'encloser' => '/',
    ];

    const OPTION_USER_MAPPER = 'userMapper';
    const OPTION_TEST_TAKER_EVENT = 'testTakerEventToTrigger';

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
        $this->options = array_merge($this->options, $options);

        $report = Report::createInfo('Starting importing users.');
        if (!file_exists($filePath)){
            throw new \Exception('File does not exists');
        }

        list($delimiter) = array_values($this->options);

        $fileHandler = fopen($filePath, 'r');
        $index = 0;
        while (!feof($fileHandler)) {
            $index++;
            $line =  fgets($fileHandler);

            if ($index === 1){
                $this->headerColumns = array_map('strtolower', array_map('trim', str_getcsv($line, $delimiter)));
                continue;
            }

            $row = array_map('trim', str_getcsv($line, $delimiter));
            try {
                if (count($this->headerColumns) !== count($row)){
                    continue;
                }
                $combineRow = array_combine($this->headerColumns, $row);
                if (isset($extraProperties[UserRdf::PROPERTY_ROLES])){
                    $combineRow['roles'] = $extraProperties[UserRdf::PROPERTY_ROLES];
                }

                $mapper = $this->getUserMapper()->map($combineRow)->combine($extraProperties);
                if ($mapper->isEmpty()){
                    continue;
                }
                $user = $this->persistUser($mapper);

                $message = 'User import success: '. $user->getUri();
                $this->logInfo($message);
                $report->add(Report::createSuccess($message));
            } catch (\Exception $exception){
                $report->add(Report::createFailure($exception->getMessage()));
            }
        }

        return $report;
    }

    /**
     * @param UserMapper $userMapper
     * @return \core_kernel_classes_Resource
     */
    protected function persistUser(UserMapper $userMapper)
    {
        $plainPassword = $userMapper->getPlainPassword();
        $properties    = $userMapper->getProperties();
        $isTestTaker   = $userMapper->isTestTaker();
        if ($isTestTaker){
            if (isset($properties[OntologyRdf::RDF_TYPE])){
                $class = $properties[OntologyRdf::RDF_TYPE];
            } else {
                $class = TaoOntology::CLASS_URI_SUBJECT;
            }
            $class = $this->getClass($class);
        } else {
            $class = $this->getClass(TaoOntology::CLASS_URI_TAO_USER);
        }

        $results = $class->searchInstances([
            UserRdf::PROPERTY_LOGIN => $properties[UserRdf::PROPERTY_LOGIN]
        ], ['like' => false]);

        if(count($results) > 0){
            $resource = $this->mergeUserProperties(current($results), $properties);
        } else {
            $resource = $class->createInstanceWithProperties($properties);
        }

        if ($isTestTaker){
            $this->triggerTestTakerEvent($resource, $properties, $plainPassword);
        } else{
            $this->triggerUserEvent($resource, $properties, $plainPassword);
        }

        return $resource;
    }

    /**
     * @param UserMapper $userMapper
     * @return mixed|void
     */
    public function setMapper(UserMapper $userMapper)
    {
        $this->mapper = $userMapper;
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @param array $properties
     * @param string $plainPassword
     */
    protected function triggerTestTakerEvent($resource, $properties, $plainPassword)
    {
        $eventName = $this->getOption(static::OPTION_TEST_TAKER_EVENT);
        if (!is_null($eventName)){
            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
            $eventObj  = new $eventName($resource->getUri(), array_merge($properties,
                ['hashForKey' => UserHashForEncryption::hash($plainPassword)]
            ));
            if ($eventObj instanceof Event){
                $eventManager->trigger($eventObj);
            }
        }
    }

    /**
     * @param \core_kernel_classes_Resource $resource
     * @param array $properties
     * @param string $plainPassword
     */
    protected function triggerUserEvent($resource, $properties, $plainPassword)
    {
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->trigger(new UserUpdatedEvent($resource,
            array_merge($properties,
                ['hashForKey' => UserHashForEncryption::hash($plainPassword)]
            )
        ));
    }

    /**
     * @param \core_kernel_classes_Resource $user
     * @param $properties
     *
     * @return \core_kernel_classes_Resource
     */
    protected function mergeUserProperties($user, $properties)
    {
        foreach ($properties as $property => $value) {
            $user->removePropertyValues($this->getProperty($property));
            $user->editPropertyValues($this->getProperty($property), $value);
        }

        return $user;
    }

    /**
     * @return UserMapper
     */
    protected function getUserMapper()
    {
        if (is_null($this->mapper)){
            $this->mapper = $this->getServiceLocator()->get(UserMapper::SERVICE_ID);
        }

        return $this->mapper;
    }

}