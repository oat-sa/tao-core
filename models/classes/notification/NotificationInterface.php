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
 * Interface NotificationInterface
 *
 * @deprecated use oat\tao\model\notification\Notification
 */
interface NotificationInterface
{

    const DEFAULT_STATUS  = 0;

    const CREATED_STATUS  = 0;
    const SENDING_STATUS  = 1;
    const SENDED_STATUS   = 2;
    const READ_STATUS     = 3;
    const ARCHIVED_STATUS = 4;

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getRecipient();

    /**
     * @return string
     */
    public function getSenderId();

    /**
     * @return string
     */
    public function getSenderName();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return int
     */
    public function getCreatedAt();

    /**
     * @return int
     */
    public function getUpdatedAt();

    /**
     * @return string
     */
    public function getId();

    /**
     * @param int $status
     *
     * @return NotificationInterface
     */
    public function setStatus($status);

    /**
     * @param string $id
     *
     * @return NotificationInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getTitle();
}
