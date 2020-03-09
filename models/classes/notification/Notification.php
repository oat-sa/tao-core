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

class Notification implements NotificationInterface, \JsonSerializable
{

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
     * AbstractNotification constructor.
     */
    public function __construct(string $userId, string $title, string $message, string $senderId, string $senderName, string $id = null, string $createdAt = null, string $updatedAt = null,  int $status = 0)
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
            $this->status = NotificationInterface::DEFAULT_STATUS;
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
