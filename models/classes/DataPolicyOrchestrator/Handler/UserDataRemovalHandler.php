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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\DataPolicyOrchestrator\Handler;

use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use Psr\Log\LoggerInterface;
use tao_models_classes_UserService;

class UserDataRemovalHandler implements DataPolicyHandlerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly tao_models_classes_UserService $userService,
    ) {
    }

    public function handle(DataPolicyMessageInterface $message): void
    {
        $login = $message->dataSubjectRawId;
        $user = $this->userService->getOneUser($login);

        if (!$user) {
            $this->logger->info(sprintf('No user data found for login "%s".', $login));

            return;
        }

        $result = $this->userService->removeUser($user);

        if (!$result) {
            throw new DataPolicyException(sprintf('User data removal failed for login "%s".', $login));
        }

        $this->logger->info(
            sprintf(
                'User data removal completed for login "%s": %s.',
                $login,
                $result ? 'success' : 'failed'
            )
        );
    }
}
