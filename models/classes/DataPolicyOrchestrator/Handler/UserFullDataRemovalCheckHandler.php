<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 */

declare(strict_types=1);

namespace oat\tao\model\DataPolicyOrchestrator\Handler;

use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use tao_models_classes_UserService;

class UserFullDataRemovalCheckHandler implements DataPolicyHandlerInterface
{
    public function __construct(private readonly tao_models_classes_UserService $userService)
    {
    }

    public function handle(DataPolicyMessageInterface $message): void
    {
        $login = $message->dataSubjectRawId;
        $user = $this->userService->getOneUser($login);

        if ($user !== null) {
            throw new DataPolicyException(
                sprintf('[Data policy - full data removal] User "%s" still exists', $login)
            );
        }
    }
}
