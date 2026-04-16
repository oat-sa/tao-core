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
use Exception;
use Psr\Log\LoggerInterface;
use Throwable;
use tao_models_classes_UserService;

class UserDataRemovalHandler implements UserDataPolicyHandlerInterface
{
    private const REQUIRED_EXTENSIONS = ['tao', 'taoEventLog'];
    private const STATUS_REMOVED = 'removed';
    private const STATUS_FAILED = 'failed';

    /** @var UserDataPolicyHandlerInterface[] */
    private array $childHandlers = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly common_ext_ExtensionsManager $extensionsManager,
        private readonly tao_models_classes_UserService $userService,
        private readonly UserDataPolicyConfirmationPublisher $confirmationPublisher,
        private readonly string $removalConfirmationTopicName
    ) {
    }

    public function addChildHandler(UserDataPolicyHandlerInterface $handler): void
    {
        $this->childHandlers[] = $handler;
    }

    public function handle(UserDataPolicyMessage $message): bool
    {
        $this->logMissingRequiredExtensions();
        $errors = [];
        $isSuccessful = true;

        foreach ($this->childHandlers as $childHandler) {
            try {
                $isSuccessful = $childHandler->handle($message) && $isSuccessful;
            } catch (Throwable $e) {
                $isSuccessful = false;
                $errors[] = sprintf(
                    'Child user data removal failed. Handler: "%s", Error: %s',
                    get_class($childHandler),
                    $e->getMessage()
                );
            }
        }

        try {
            $isSuccessful = $isSuccessful && $this->removeMainUserData($message);
        } catch (Throwable $e) {
            $isSuccessful = false;
            $errors[] = sprintf(
                'Main user data removal failed. Handler: "%s", Error: %s',
                __CLASS__,
                $e->getMessage()
            );
        }

        $this->confirmationPublisher->publishPayload(
            $this->removalConfirmationTopicName,
            $message->toRemovalConfirmationPayload(
                $isSuccessful ? self::STATUS_REMOVED : self::STATUS_FAILED,
                $errors
            )
        );

        return $isSuccessful;
    }

    private function removeMainUserData(UserDataPolicyMessage $message): bool
    {
        $login = $message->getDataSubjectRawId();
        $user = $this->userService->getOneUser($login);

        if (!$user) {
            $this->logger->info(sprintf('No user data found for login "%s".', $login));

            return true;
        }

        $result = $this->userService->removeUser($user);
        $this->logger->info(
            sprintf(
                'User data removal completed for login "%s": %s.',
                $login,
                $result ? 'success' : 'failed'
            )
        );

        return $result;
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
