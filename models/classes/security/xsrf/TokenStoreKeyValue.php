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
    private const TOKENS_STORAGE_COLLECTION_KEY_SUFFIX = 'keys';

    private common_persistence_AdvKeyValuePersistence $persistence;
    private string $keyPrefix;

    public function getToken(string $tokenId): ?Token
    {
        $tokenData = $this->getPersistence()->get($this->buildKey($tokenId));

        return is_string($tokenData) ? new Token(json_decode($tokenData, true)) : null;
    }

    public function setToken(string $tokenId, Token $token): void
    {
        $tokenKey = $this->buildKey($tokenId);

        $this->getPersistence()->set(
            $tokenKey,
            json_encode($token),
            $this->getTtl()
        );

        $this->addTokenKeyToCollection($tokenKey);
    }

    public function hasToken(string $tokenId): bool
    {
        return $this->getPersistence()->exists($this->buildKey($tokenId));
    }

    public function removeToken(string $tokenId): bool
    {
        $this->removeTokenKeyFromCollection($tokenId);

        return $this->getPersistence()->del($this->buildKey($tokenId));
    }

    public function clear(): void
    {
        foreach ($this->getTokenKeys() as $tokenKey) {
            $this->getPersistence()->del($tokenKey);
        }

        $this->clearTokenCollection();
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $tokens = [];

        foreach ($this->getTokenKeys() as $key) {
            $tokenData = $this->getPersistence()->get($key);

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
    protected function getKeyPrefix(): string
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

    private function getTokenCollectionKey(): string
    {
        return $this->buildKey(self::TOKENS_STORAGE_COLLECTION_KEY_SUFFIX);
    }

    private function buildKey(string $name): string
    {
        return sprintf('%s_%s', $this->getKeyPrefix(), $name);
    }

    private function addTokenKeyToCollection(string $tokenKey): bool
    {
        return $this->getPersistence()->set(
            $this->getTokenCollectionKey(),
            json_encode(array_merge($this->getTokenKeys(), [$tokenKey])),
            $this->getTtl()
        );
    }

    private function removeTokenKeyFromCollection(string $tokenKey): void
    {
        $collection = $this->getTokenKeys();

        if (empty($collection)) {
            return;
        }

        $key = array_search($tokenKey, $collection);

        if ($key === false) {
            return;
        }

        unset($collection[$key]);

        $this->getPersistence()->set(
            $this->getTokenCollectionKey(),
            json_encode($collection),
            $this->getTtl()
        );
    }

    private function clearTokenCollection(): void
    {
        $this->getPersistence()->del($this->getTokenCollectionKey());
    }

    private function getTokenKeys(): array
    {
        return json_decode((string)$this->getPersistence()->get($this->getTokenCollectionKey())) ?? [];
    }

    private function getTtl(): ?int
    {
        return ((int) $this->getOption(self::OPTION_TTL)) ?: null;
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
