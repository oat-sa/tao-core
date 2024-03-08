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

namespace oat\tao\helpers;

use common_exception_Error;
use common_session_Session;
use oat\generis\model\user\UserRdf;
use oat\tao\model\session\Context\TenantDataSessionContext;

class UserPilotTemplateHelper extends Layout
{
    public const USER_PILOT_TEMPLATE = 'blocks/userpilot.tpl';
    public const DEFAULT_LOCALE = 'en-US';
    public const NOT_AVAILABLE = 'N/A';

    /**
     * @throws common_exception_Error
     */
    public static function userPilotCode(common_session_Session $session = null): void
    {
        $userPilotToken = getenv('USER_PILOT_TOKEN');
        if (!$userPilotToken || !method_exists(self::$templateClass, 'inc')) {
            return;
        }

        if (!$session) {
            return;
        }

        $user = $session->getUser();
        $tenantId = self::NOT_AVAILABLE;
        $tenantContext = $session->getContexts(TenantDataSessionContext::class)[0] ?? null;
        if ($tenantContext instanceof TenantDataSessionContext) {
            $tenantId = $tenantContext->getTenantId();
        }
        $userIdentifier = $user->getIdentifier() ?? self::NOT_AVAILABLE;
        $userId = $tenantId . '|' . $userIdentifier;
        $userName = $session->getUserLabel() ?? self::NOT_AVAILABLE;
        $userLogin = $user->getPropertyValues(UserRdf::PROPERTY_LOGIN)[0] ?? self::NOT_AVAILABLE;
        $userEmail = $user->getPropertyValues(UserRdf::PROPERTY_MAIL)[0] ?? self::NOT_AVAILABLE;
        $interfaceLanguage = $session->getInterfaceLanguage() ?? self::DEFAULT_LOCALE;
        $userRoles = join(',', $session->getUserRoles() ?? [self::NOT_AVAILABLE]);

        call_user_func(
            [self::$templateClass, 'inc'],
            self::USER_PILOT_TEMPLATE,
            'tao',
            [
                'userpilot_data' => [
                    'token' => $userPilotToken,
                    'user' => [
                        'id' => $userId,
                        'name' => $userName,
                        'login' => $userLogin,
                        'email' => $userEmail,
                        'roles' => $userRoles,
                        'interface_language' => $interfaceLanguage,
                    ],
                    'tenant' => [
                        'id' => $tenantId,
                        'name' => $tenantId,
                    ]
                ],
            ]
        );
    }
}
