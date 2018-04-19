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

namespace oat\tao\model\user\import;

use oat\generis\Helper\UserHashForEncryption;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\user\UserRdf;
use oat\oatbox\event\EventManager;
use oat\tao\model\event\UserUpdatedEvent;
use oat\tao\model\TaoOntology;

class RdsUserImportService extends AbstractImportService implements UserImportServiceInterface
{
    use OntologyAwareTrait;

    /**
     * Format the $data with $extraProperties
     *
     * @param array $data
     * @param array $extraProperties
     * @return array
     */
    protected function formatData(array $data, array $extraProperties)
    {
        if (isset($extraProperties[UserRdf::PROPERTY_ROLES])) {
            $data['roles'] = $extraProperties[UserRdf::PROPERTY_ROLES];
        }

        return $data;
    }

    /**
     * Persist a user, create or update
     *
     * @param ImportMapper $userMapper
     * @return \core_kernel_classes_Resource
     * @throws \Exception
     */
    protected function persist(ImportMapper $userMapper)
    {
        if (!$userMapper instanceof UserMapper) {
            throw new \Exception('Mapper should be a UserMapper');
        }

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
     * @param array $data
     * @param array $csvControls
     * @param string $delimiter
     * @throws \Exception
     */
    protected function applyCsvImportRules(array $data, array $csvControls, $delimiter)
    {
        if (count($data) == 1) {
            $csvControlsString = implode(', ', array_map(
                function ($v, $k) { return sprintf("%s: '%s'", $k, $v); },
                $csvControls,
                array_keys($csvControls)
            ));
            throw new \Exception(
                'It seems that the csv is malformed. The delimiter \'' . $delimiter . '\' does not explode the line correctly (only one cell).' .
                "\n" . ' Csv controls are ' . $csvControlsString

            );
        }
    }
}