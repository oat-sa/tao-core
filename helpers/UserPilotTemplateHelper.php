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
use oat\tao\model\session\Dto\UserPilotDto;

class UserPilotTemplateHelper extends Layout
{
    public const USER_PILOT_TEMPLATE = 'blocks/userpilot.tpl';

    /**
     * @throws common_exception_Error
     */
    public static function userPilotCode(UserPilotDto $dto): void
    {
        $userPilotToken = getenv('USER_PILOT_TOKEN');
        if (!$userPilotToken || !method_exists(self::$templateClass, 'inc')) {
            return;
        }

        if (!$dto->getUserId()) {
            return;
        }

        call_user_func(
            [self::$templateClass, 'inc'],
            self::USER_PILOT_TEMPLATE,
            'tao',
            [
                'userpilot_data' => [
                    'token' => $userPilotToken,
                    'user' => [
                        'id' => $dto->getUserId(),
                        'name' => $dto->getUserName(),
                        'login' => $dto->getUserLogin(),
                        'email' => $dto->getUserEmail(),
                        'roles' => join(',', $dto->getUserRoles()),
                        'interface_language' => $dto->getInterfaceLanguage(),
                    ],
                    'tenant' => [
                        'id' => $dto->getTenantId(),
                    ]
                ],
            ]
        );
    }
}
