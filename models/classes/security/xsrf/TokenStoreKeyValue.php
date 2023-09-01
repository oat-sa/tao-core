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
 * Copyright (c) 2018-2023 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\security\xsrf;

use common_exception_Error;
use common_persistence_PhpRedisDriver;
use Psr\Container\ContainerInterface;
use oat\oatbox\session\SessionService;
use oat\oatbox\service\ConfigurableService;
use common_persistence_AdvKeyValuePersistence;
use oat\generis\persistence\PersistenceManager;

/**
 * Class to store tokens in a key value storage
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class TokenStoreKeyValue extends ConfigurableService implements TokenStore
{
    public const OPTION_PERSISTENCE = 'persistence';
    public const OPTION_TTL = 'ttl';

    public const TOKENS_STORAGE_KEY = 'tao_tokens';

    private common_persistence_AdvKeyValuePersistence $persistence;
    private string $keyPrefix;

    public function getToken(string $tokenId): ?Token
    {
        $tokenData = $this->getPersistence()->hGet($this->getKey(), $tokenId);

        return is_string($tokenData) ? new Token(json_decode($tokenData, true)) : null;
    }

    public function setToken(string $tokenId, Token $token): void
    {
        $this->getPersistence()->hSet($this->getKey(), $tokenId, json_encode($token));
    }

    public function hasToken(string $tokenId): bool
    {
        return $this->getPersistence()->hExists($this->getKey(), $tokenId);
    }

    public function removeToken(string $tokenId): bool
    {
        return $this->getPersistence()->hDel($this->getKey(), $tokenId);
    }

    public function clear(): void
    {
        $this->getPersistence()->del($this->getKey());
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $tokensData = $this->getPersistence()->hGetAll($this->getKey());

        if (!is_array($tokensData)) {
            return [];
        }

        $tokens = [];

        foreach ($tokensData as $tokenData) {
            if (is_string($tokenData)) {
                $tokens[] = new Token(json_decode($tokenData, true));
            }
        }

        return $tokens;
    }

    /**
     * @throws common_exception_Error
     */
    protected function getPersistence(): common_persistence_AdvKeyValuePersistence
    {
        if (!isset($this->persistence)) {
            $persistence = $this->getPersistenceManager()->getPersistenceById(
                $this->getOption(self::OPTION_PERSISTENCE)
            );

            if (!$persistence instanceof common_persistence_AdvKeyValuePersistence) {
                throw new common_exception_Error(
                    'TokenStoreKeyValue expects advanced key value persistence implementation.'
                );
            }

            $this->persistence = $persistence;
        }

        return $this->persistence;
    }

    /**
     * @throws common_exception_Error
     */
    protected function getKey(): string
    {
        if (!isset($this->keyPrefix)) {
            $this->keyPrefix = sprintf(
                '%s_%s',
                $this->getSessionService()->getCurrentUser()->getIdentifier(),
                self::TOKENS_STORAGE_KEY
            );
        }

        return $this->keyPrefix;
    }

    /**
     * @param int $uSleepInterval Microseconds interval between scan/keys to perform deletion
     * @param int $timeLimit Expiration time for tokens, 0 means, no expiration
     * @return int - The total deleted records
     */
    public function clearAll(int $uSleepInterval, int $timeLimit = 0): int
    {
        $persistence = $this->getPersistence();
        $driver = $persistence->getDriver();
        $pattern = sprintf('*%s*', self::TOKENS_STORAGE_KEY);
        $countDeleted = 0;

        if ($driver instanceof common_persistence_PhpRedisDriver) {
            $iterator = null;

            while ($iterator !== 0) {
                foreach ($driver->scan($iterator, $pattern) as $key) {
                    $countDeleted += $this->deleteAllExpiredByKey($key, $timeLimit);
                }

                usleep($uSleepInterval);
            }

            return $countDeleted;
        }

        $keys = $persistence->keys($pattern);
        $keys = is_array($keys) ? $keys : [];

        foreach ($keys as $key) {
            $countDeleted += $this->deleteAllExpiredByKey($key, $timeLimit);
        }

        return $countDeleted;
    }

    private function deleteAllExpiredByKey(string $key, int $timeLimit): int
    {
        $persistence = $this->getPersistence();
        $tokensData = $persistence->hGetAll($key);
        $countDeleted = 0;

        if (empty($tokensData)) {
            if ($persistence->del($key)) {
                return 1;
            }
        }

        foreach ($tokensData as $tokenData) {
            if (is_string($tokenData)) {
                $token = new Token(json_decode($tokenData, true));

                if (!$token->isExpired($timeLimit)) {
                    continue;
                }

                if ($persistence->hDel($key, $token->getValue())) {
                    $countDeleted++;
                }
            }
        }

        return $countDeleted;
    }

    private function getPersistenceManager(): PersistenceManager
    {
        return $this->getContainer()->get(PersistenceManager::SERVICE_ID);
    }

    private function getSessionService(): SessionService
    {
        return $this->getContainer()->get(SessionService::SERVICE_ID);
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getServiceManager()->getContainer();
    }
}
