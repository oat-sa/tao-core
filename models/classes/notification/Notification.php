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

use JsonSerializable;

class Notification implements JsonSerializable
{
    public const DEFAULT_STATUS  = 0;

    public const CREATED_STATUS  = 0;
    public const SENDING_STATUS  = 1;
    public const SENDED_STATUS   = 2;
    public const READ_STATUS     = 3;
    public const ARCHIVED_STATUS = 4;

    protected $id;

    protected $status;

    protected $recipient;

    protected $senderId;

    protected $senderName;

    protected $title;

    protected $message;

    protected $createdAt;

    protected $updatedAt;

    /**
     * Notification constructor.
     *
     * @param string $userId
     * @param string $title
     * @param string $message
     * @param string $senderId
     * @param string $senderName
     * @param string|null $id
     * @param string|null $createdAt
     * @param string|null $updatedAt
     * @param int $status
     */
    public function __construct($userId, $title, $message, $senderId, $senderName, $id = null, $createdAt = null, $updatedAt = null, $status = 0)
    {
        $this->id         = $id;
        $this->status     = $status;
        $this->recipient  = $userId;
        $this->senderId   = $senderId;
        $this->senderName = $senderName;
        $this->title      = $title;
        $this->message    = $message;
        $this->createdAt  = $createdAt;
        $this->updatedAt  = $updatedAt;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getRecipient()
    {
        return $this->recipient;
    }

    public function getSenderId()
    {
        return $this->senderId;
    }

    public function getSenderName()
    {
        return $this->senderName;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt()
    {
        return strtotime($this->createdAt);
    }

    public function getUpdatedAt()
    {
        return strtotime($this->updatedAt);
    }

    public function setStatus($status)
    {
        if (is_int($status)) {
            $this->status = $status;
        } else {
            $this->status = self::DEFAULT_STATUS;
        }
        $this->updatedAt = date('Y-m-d H:i:s');
        return $this;
    }

    public function setId($id)
    {
        if ($this->id === null) {
            $this->id = $id;
        }
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => $this->getId(),
                'status' => $this->getStatus(),
                'recipient' => $this->getRecipient(),
                'sender' => $this->getSenderId(),
                'senderName' => $this->getSenderId(),
                'title' => $this->getTitle(),
                'message' => $this->getMessage(),
                'createdAt' => $this->getCreatedAt(),
                'updatedAt' => $this->getUpdatedAt(),
            ];
    }
}
