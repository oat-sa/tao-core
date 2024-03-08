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
use oat\generis\model\user\UserRdf;
use oat\tao\model\session\Context\TenantDataSessionContext;
use oat\tao\model\session\Context\UserDataSessionContext;

class UserPilotDto
{
    public const NOT_AVAILABLE = 'N/A';
    public const DEFAULT_LOCALE = 'en-US';
    private string $userId = self::NOT_AVAILABLE . '|' . self::NOT_AVAILABLE;
    private string $userName = self::NOT_AVAILABLE;
    private string $userLogin = self::NOT_AVAILABLE;
    private string $userEmail = self::NOT_AVAILABLE;
    private string $interfaceLanguage = self::DEFAULT_LOCALE;
    private array $userRoles = [self::NOT_AVAILABLE];
    private string $tenantId = self::NOT_AVAILABLE;
    private string $tenantName = self::NOT_AVAILABLE;

    public function __construct($session = null)
    {
        if ($session instanceof common_session_Session) {
            $user = $session->getUser();
            $this->userId = $user->getIdentifier() ?? self::NOT_AVAILABLE;
            $this->userLogin = $user->getPropertyValues(UserRdf::PROPERTY_LOGIN)[0] ?? self::NOT_AVAILABLE;
            $this->userEmail = $user->getPropertyValues(UserRdf::PROPERTY_MAIL)[0] ?? self::NOT_AVAILABLE;
            $this->interfaceLanguage = $session->getInterfaceLanguage() ?? self::DEFAULT_LOCALE;

            $contexts = $session->getContexts() ?? [];
            foreach ($contexts as $context) {
                if ($context instanceof UserDataSessionContext) {
                    $this->userId = $context->getUserId() ?? $this->userId;
                    $this->userLogin = $context->getUserLogin() ?? $this->userLogin;
                    $this->userName = $context->getUserName() ?? self::NOT_AVAILABLE;
                    $this->userEmail = $context->getUserEmail() ?? $this->userEmail;
                    $this->interfaceLanguage = $context->getLocale() ?? $this->interfaceLanguage;
                } elseif ($context instanceof TenantDataSessionContext) {
                    $this->tenantId = $context->getTenantId() ?? self::NOT_AVAILABLE;
                    $this->tenantName = $context->getTenantName() ?? self::NOT_AVAILABLE;
                }
            }

            $this->userId = $this->tenantId . '|' . $this->userId;
            $this->userRoles = $session->getUserRoles() ?? [self::NOT_AVAILABLE];
        }
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUserLogin(): string
    {
        return $this->userLogin;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getInterfaceLanguage(): string
    {
        return $this->interfaceLanguage;
    }

    public function getUserRoles(): array
    {
        return $this->userRoles;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getTenantName(): string
    {
        return $this->tenantName;
    }
}
