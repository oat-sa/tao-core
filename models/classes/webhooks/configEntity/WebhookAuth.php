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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\webhooks\configEntity;

class WebhookAuth implements WebhookAuthInterface
{
    public const AUTH_CLASS = 'authClass';
    public const CREDENTIALS = 'credentials';

    /**
     * @var string
     * @see \oat\tao\model\auth\AbstractAuthType
     */
    private $authClass;

    /**
     * @var array
     */
    private $credentials;

    /**
     * @param string $authClass
     * @param array $properties
     */
    public function __construct($authClass, array $properties)
    {
        $this->authClass = $authClass;
        $this->credentials = $properties;
    }

    /**
     * @return string
     */
    public function getAuthClass()
    {
        return $this->authClass;
    }

    /**
     * @return array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::AUTH_CLASS => $this->getAuthClass(),
            self::CREDENTIALS => $this->getCredentials()
        ];
    }
}
