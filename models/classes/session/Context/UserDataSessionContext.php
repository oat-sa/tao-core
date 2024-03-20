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
 * Copyright (c) 2013-2024 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\session\Context;

use oat\oatbox\session\SessionContext;

class UserDataSessionContext implements SessionContext
{
    private ?string $userId;
    private ?string $userLogin;
    private ?string $userName;
    private ?string $userEmail;
    private ?string $locale;

    public function __construct(
        string $userId = null,
        string $userLogin = null,
        string $userName = null,
        string $userEmail = null,
        string $locale = null
    ) {
        $this->userId = $userId;
        $this->userLogin = $userLogin;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->locale = $locale;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getUserLogin(): ?string
    {
        return $this->userLogin;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
