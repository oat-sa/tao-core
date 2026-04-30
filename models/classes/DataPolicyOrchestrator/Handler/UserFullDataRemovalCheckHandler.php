<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 */

declare(strict_types=1);

namespace oat\tao\model\DataPolicyOrchestrator\Handler;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\user\UserRdf;
use oat\tao\model\DataPolicyOrchestrator\Exception\DataPolicyException;
use oat\tao\model\DataPolicyOrchestrator\Model\DataPolicyMessageInterface;
use oat\tao\model\TaoOntology;

class UserFullDataRemovalCheckHandler implements DataPolicyHandlerInterface
{
    public function __construct(private readonly Ontology $ontology)
    {
    }

    public function handle(DataPolicyMessageInterface $message): void
    {
        $login = $message->dataSubjectRawId;
        $user = $this->findUserResourceByLogin($login);

        if ($user !== null) {
            throw new DataPolicyException(
                sprintf('[Data policy - full data removal] User "%s" still exists', $login)
            );
        }
    }

    private function findUserResourceByLogin(string $login): ?core_kernel_classes_Resource
    {
        $users = $this->ontology
            ->getClass(TaoOntology::CLASS_URI_TAO_USER)
            ->searchInstances(
                [UserRdf::PROPERTY_LOGIN => $login],
                ['like' => false, 'recursive' => true]
            );

        $usersCount = count($users);
        if ($usersCount === 0) {
            return null;
        }

        if ($usersCount > 1) {
            throw new DataPolicyException(
                sprintf('More than one user was found for login "%s".', $login)
            );
        }

        $user = reset($users);

        return $user instanceof core_kernel_classes_Resource ? $user : null;
    }
}
