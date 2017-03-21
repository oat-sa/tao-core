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


interface NotificationInterface
{

    const DEFAULT_STATUS  = 0;

    const CREATED_STATUS  = 0;
    const SENDING_STATUS  = 1;
    const SENDED_STATUS   = 2;
    const READ_STATUS     = 3;
    const ARCHIVED_STATUS = 4;

    public function getStatus();

    public function getRecipient();

    public function getSenderId();

    public function getSenderName();

    public function getMessage();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getId();

    public function setStatus($status);

    public function setId($id);

    public function getTitle();

}