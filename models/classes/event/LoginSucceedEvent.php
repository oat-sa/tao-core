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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\tao\model\event;

use JsonSerializable;
use oat\oatbox\event\Event;

class LoginSucceedEvent implements Event, JsonSerializable
{
    private $login = '';
    private $time;

    /**
     * LoginEvent constructor.
     * @param $login
     */
    public function __construct($login = '')
    {
        $this->login = $login;
        $this->time = time();
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Return a unique name for this event
     * @see \oat\oatbox\event\Event::getName()
     */
    public function getName()
    {
        return __CLASS__;
    }


    /**
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    public function jsonSerialize(): array
    {
        return [
            'login' => $this->getLogin()
        ];
    }
}
