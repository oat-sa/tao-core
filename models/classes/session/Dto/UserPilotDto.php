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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\session\Dto;

use common_session_Session;
use oat\tao\model\session\Context\TenantDataSessionContext;
use oat\tao\model\session\Context\UserDataSessionContext;

class UserPilotDto
{
    public const NOT_AVAILABLE = 'N/A';
    public const DEFAULT_LOCALE = 'en-US';
    private ?string $userId = null;
    private ?string $userName = null;
    private ?string $userLogin = null;
    private ?string $userEmail = null;
    private ?string $interfaceLanguage = null;
    private array $userRoles = [];
    private ?string $tenantId = null;

    public function __construct($session = null)
    {
        if ($session instanceof common_session_Session) {
            $contexts = $session->getContexts() ?? [];
            foreach ($contexts as $context) {
                if ($context instanceof UserDataSessionContext) {
                    $this->userId = $context->getUserId();
                    $this->userLogin = $context->getUserLogin() ?? self::NOT_AVAILABLE;
                    $this->userName = $context->getUserName() ?? self::NOT_AVAILABLE;
                    $this->userEmail = $context->getUserEmail() ?? self::NOT_AVAILABLE;
                    $this->interfaceLanguage = $context->getLocale() ?? self::DEFAULT_LOCALE;
                } elseif ($context instanceof TenantDataSessionContext) {
                    $this->tenantId = $context->getTenantId();
                }
            }

            if (null !== $this->userId && null !== $this->tenantId) {
                $this->userId = $this->tenantId . '|' . $this->userId;
            }

            $this->userRoles = $session->getUserRoles() ?? [];
        }
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

    public function getInterfaceLanguage(): ?string
    {
        return $this->interfaceLanguage;
    }

    public function getUserRoles(): array
    {
        return $this->userRoles;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
}
