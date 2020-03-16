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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\notification\implementation;

use oat\generis\persistence\sql\SchemaCollection;
use oat\generis\persistence\sql\SchemaProviderInterface;
use oat\oatbox\service\exception\InvalidService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\tao\model\notification\AbstractNotificationService;
use oat\tao\model\notification\exception\NotListedNotification;
use oat\tao\model\notification\Notification;
use oat\tao\model\notification\NotificationServiceInterface;

/**
 * Class NotificationServiceAggregator
 *
 * @deprecated This class is used by client only. It will be moved to client specific extension
 */
class NotificationServiceAggregator extends AbstractNotificationService implements SchemaProviderInterface
{
    public const OPTION_RDS_NOTIFICATION_SERVICE = 'rds';

    /**
     * @return array
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function getSubServices()
    {
        $subServices = $this->getOptions();
        $services = [];
        foreach ($subServices as $name => $subService) {
            $services[] = $this->getSubService($name, NotificationServiceInterface::class);
        }
        return $services;
    }

    /**
     * @param Notification $notification
     *
     * @return Notification
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function sendNotification(Notification $notification)
    {
        $subServices = $this->getSubServices();

        /**
         * @var NotificationServiceInterface $service
         */
        foreach ($subServices as $service) {
            $service->sendNotification($notification);
        }

        return $notification;
    }

    /**
     * @param string $userId
     *
     * @return array
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws NotListedNotification
     */
    public function getNotifications($userId)
    {
        $subServices = $this->getSubServices();

        /**
         * @var NotificationServiceInterface $service
         */
        foreach ($subServices as $service) {
            if (($list = $service->getNotifications($userId)) !== false) {
                return $list;
            }
        }

        throw new NotListedNotification();
    }

    /**
     * @param string $id
     *
     * @return NotificationServiceInterface
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws NotListedNotification
     */
    public function getNotification($id)
    {

        $subServices = $this->getSubServices();

        /**
         * @var NotificationServiceInterface $service
         */
        foreach ($subServices as $service) {
            if (($notification = $service->getNotification($id)) !== false) {
                return $notification;
            }
        }

        throw new NotListedNotification();
    }

    /**
     * @param Notification $notification
     *
     * @return bool
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws NotListedNotification
     */
    public function changeStatus(Notification $notification)
    {
        $subServices = $this->getSubServices();

        /**
         * @var NotificationServiceInterface $service
         */
        foreach ($subServices as $service) {
            if (($newNotification = $service->changeStatus($notification)) !== false) {
                return $newNotification;
            }
        }

        throw new NotListedNotification();
    }

    /**
     * @param string $userId
     *
     * @return array
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws NotListedNotification
     */
    public function notificationCount($userId)
    {
        $subServices = $this->getSubServices();

        /**
         * @var NotificationServiceInterface $service
         */
        foreach ($subServices as $service) {
            if (($newNotification = $service->notificationCount($userId)) !== false) {
                return $newNotification;
            }
        }

        throw new NotListedNotification();
    }

    /**
     * @return bool
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function getVisibility()
    {
        $subServices = $this->getSubServices();

        /**
         * @var NotificationServiceInterface $service
         */
        foreach ($subServices as $service) {
            if ($service->getVisibility()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SchemaCollection $schemaCollection
     *
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function provideSchema(SchemaCollection $schemaCollection)
    {
        $subServices = $this->getSubServices();
        foreach ($subServices as $service) {
            if ($service instanceof SchemaProviderInterface) {
                $service->provideSchema($schemaCollection);
            }
        }

    }
}
