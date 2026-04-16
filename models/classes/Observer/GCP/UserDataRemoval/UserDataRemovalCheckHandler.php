<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 */

declare(strict_types=1);

namespace oat\tao\model\Observer\GCP\UserDataRemoval;

use common_ext_ExtensionsManager;
use Psr\Log\LoggerInterface;
use Throwable;
use tao_models_classes_UserService;

class UserDataRemovalCheckHandler implements UserDataPolicyHandlerInterface
{
    private const REQUIRED_EXTENSIONS = ['tao', 'taoEventLog'];

    /** @var UserDataPolicyHandlerInterface[] */
    private array $childHandlers = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly common_ext_ExtensionsManager $extensionsManager,
        private readonly tao_models_classes_UserService $userService,
        private readonly UserDataPolicyConfirmationPublisher $confirmationPublisher,
        private readonly string $fullRemovalConfirmationTopicName
    ) {
    }

    public function addChildHandler(UserDataPolicyHandlerInterface $handler): void
    {
        $this->childHandlers[] = $handler;
    }

    public function handle(UserDataPolicyMessage $message): bool
    {
        $this->logMissingRequiredExtensions();
        $isSuccessful = true;

        foreach ($this->childHandlers as $childHandler) {
            $isSuccessful = $isSuccessful && $childHandler->handle($message);
        }

        $isSuccessful = $isSuccessful && $this->checkMainUserDataRemoval($message);

        if ($isSuccessful) {
            $this->confirmationPublisher->publishPayload(
                $this->fullRemovalConfirmationTopicName,
                $message->toFullRemovalConfirmationPayload()
            );
        }

        return $isSuccessful;
    }

    private function checkMainUserDataRemoval(UserDataPolicyMessage $message): bool
    {
        $login = $message->getDataSubjectRawId();

        try {
            $user = $this->userService->getOneUser($login);
            $isRemoved = $user === null;

            $this->logger->info(
                sprintf(
                    'User data check for login "%s": %s.',
                    $login,
                    $isRemoved ? 'removed' : 'still exists'
                )
            );

            return $isRemoved;
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf('User data check failed for login "%s": %s', $login, $exception->getMessage())
            );

            return false;
        }
    }

    private function logMissingRequiredExtensions(): void
    {
        foreach (self::REQUIRED_EXTENSIONS as $extensionId) {
            if ($this->extensionsManager->isInstalled($extensionId)) {
                continue;
            }

            $this->logger->warning(
                sprintf(
                    'Extension "%s" is not installed. User data handled by this extension cannot be removed automatically and should be removed manually if needed.',
                    $extensionId
                )
            );
        }
    }
}
