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
use common_session_AnonymousSession;
use common_session_Session;
use common_session_SessionManager;
use oat\generis\model\user\UserRdf;
use oat\tao\model\session\Context\TenantDataSessionContext;

class UserPilotTemplateHelper extends Layout
{
    private static ?common_session_Session $session;

    private static bool $isSessionSet = false;

    public static function setSession(?common_session_Session $session = null): void
    {
        self::$session = $session;
        self::$isSessionSet = true;
    }

    /**
     * @throws common_exception_Error
     */
    private static function getSession(): ?common_session_Session
    {
        if (!self::$isSessionSet) {
            self::$session = common_session_SessionManager::getSession();
        }

        return self::$session;
    }

    /**
     * @throws common_exception_Error
     */
    public static function getUserPilotCode(): void
    {
        $userPilotToken = getenv('USER_PILOT_TOKEN');
        if (!$userPilotToken || !method_exists(self::$templateClass, 'inc')) {
            return;
        }

        $session = self::getSession();
        echo print_r($session, true);
        if (!self::$session) {
            return;
        }

        if (
            self::$session instanceof common_session_AnonymousSession
            || self::$session instanceof common_session_Session
        ) {
            $user = $session->getUser();
            $tenantId = 'N/A';
            $tenantContext = $session->getContexts(TenantDataSessionContext::class)[0] ?? null;
            if ($tenantContext instanceof TenantDataSessionContext) {
                $tenantId = $tenantContext->getTenantId();
            }
            $userIdentifier = $user->getIdentifier() ?? 'N/A';
            $userId = $tenantId . '|' . $userIdentifier;
            $userName = $session->getUserLabel() ?? 'N/A';
            $userLogin = $user->getPropertyValues(UserRdf::PROPERTY_LOGIN)[0] ?? 'N/A';
            $userEmail = $user->getPropertyValues(UserRdf::PROPERTY_MAIL)[0] ?? 'N/A';
            $interfaceLanguage = $session->getInterfaceLanguage() ?? 'en-US';
            $userRoles = join(',', $session->getUserRoles() ?? ['N/A']);

            call_user_func(
                [self::$templateClass, 'inc'],
                'blocks/userpilot.tpl',
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
}
