<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 */

declare(strict_types=1);

namespace oat\tao\model\DataPolicyOrchestrator\Handler;

use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessage;
use Psr\Log\LoggerInterface;
use tao_models_classes_UserService;

class DataRemovalHandler implements DataPolicyHandlerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly tao_models_classes_UserService $userService,
    ) {
    }

    public function handle(DataPolicyMessage $message): void
    {
        $login = $message->dataSubjectRawId;
        $user = $this->userService->getOneUser($login);

        if (!$user) {
            $this->logger->info(sprintf('No user data found for login "%s".', $login));

            return;
        }

        $result = $this->userService->removeUser($user);
        $this->logger->info(
            sprintf(
                'User data removal completed for login "%s": %s.',
                $login,
                $result ? 'success' : 'failed'
            )
        );
    }
}
