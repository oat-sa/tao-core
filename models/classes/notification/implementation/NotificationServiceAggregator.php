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
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function getSubServices(): array
    {
        $subServices = $this->getOptions();
        $services = [];
        foreach ($subServices as $name => $subService) {
            $services[] = $this->getSubService($name, NotificationServiceInterface::class);
        }
        return $services;
    }

    /**
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function sendNotification(Notification $notification): Notification
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
     * @return Notification[]
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws NotListedNotification
     */
    public function getNotifications(string $userId): array
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
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws NotListedNotification
     */
    public function getNotification(string $id): Notification
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
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws NotListedNotification
     */
    public function changeStatus(Notification $notification): bool
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
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     * @throws NotListedNotification
     */
    public function notificationCount(string $userId): array
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
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function getVisibility(): bool
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
     * @throws InvalidService
     * @throws InvalidServiceManagerException
     */
    public function provideSchema(SchemaCollection $schemaCollection): void
    {
        $subServices = $this->getSubServices();
        foreach ($subServices as $service) {
            if ($service instanceof SchemaProviderInterface) {
                $service->provideSchema($schemaCollection);
            }
        }
    }
}
