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

namespace oat\tao\model\notification;

/**
 * Interface NotificationServiceInterface
 * @deprecated This class is used by client only. It will be moved to client specific extension
 */
interface NotificationServiceInterface
{
    public const SERVICE_ID  = 'tao/notification';

    public function sendNotification(Notification $notification): Notification;

    /**
     * @return NotificationServiceInterface[]
     */
    public function getNotifications(string $userId): array;

    public function getNotification(string $id): Notification;

    public function changeStatus(Notification $notification): bool;

    public function notificationCount(string $userId): array;

    public function getVisibility(): bool;
}
