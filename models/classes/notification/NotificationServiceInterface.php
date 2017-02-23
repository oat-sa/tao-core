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


interface NotificationServiceInterface
{

    const SERVICE_ID  = 'tao/notification';

    /**
     * @param NotificationInterface $notification
     * @return NotificationInterface
     */
    public function sendNotification(NotificationInterface $notification);

    /**
     * @param string $userId
     * @return array an array of NotificationServiceInterface
     */
    public function getNotifications($userId);

    /**
     * @param string $id
     * @return NotificationServiceInterface
     */
    public function getNotification($id);

    /**
     * @param NotificationInterface $notification
     * @return boolean
     */
    public function changeStatus(NotificationInterface $notification);

    /**
     * @param string $userId
     * @return array
     */
    public function notificationCount( $userId);

    /**
     * @return boolean
     */
    public function getVisibility();

}