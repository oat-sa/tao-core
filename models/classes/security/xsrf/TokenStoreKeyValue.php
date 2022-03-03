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
 * Copyright (c) 2018-2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\security\xsrf;

use common_exception_Error;
use Psr\Container\ContainerInterface;
use oat\oatbox\session\SessionService;
use common_persistence_KeyValuePersistence;
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

    private const TOKENS_STORAGE_KEY = 'tao_tokens';

    /** @var common_persistence_KeyValuePersistence */
    private $persistence;

    /** @var string */
    private $keyPrefix;

    public function getToken(string $tokenId): ?Token
    {
        if (!$this->hasToken($tokenId)) {
            return null;
        }

        $tokenData = $this->getPersistence()->get($this->buildKey($tokenId));

        return new Token(json_decode($tokenData, true));
    }

    public function setToken(string $tokenId, Token $token): void
    {
        $this->getPersistence()->set(
            $this->buildKey($tokenId),
            json_encode($token),
            $this->getTtl()
        );
    }

    public function hasToken(string $tokenId): bool
    {
        return $this->getPersistence()->exists($this->buildKey($tokenId));
    }

    public function removeToken(string $tokenId): bool
    {
        if (!$this->hasToken($tokenId)) {
            return false;
        }

        return $this->getPersistence()->del($this->buildKey($tokenId));
    }

    public function clear(): void
    {
        foreach ($this->getKeys() as $key) {
            $this->getPersistence()->del($key);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $tokens = [];

        foreach ($this->getKeys() as $key) {
            $tokenData = $this->getPersistence()->get($key);
            $tokens[] = new Token(json_decode($tokenData, true));
        }

        return $tokens;
    }

    private function getKeys(): array
    {
        return $this->getPersistence()->keys($this->getKeyPrefix() . '*');
    }

    private function buildKey(string $name): string
    {
        return sprintf('%s_%s', $this->getKeyPrefix(), $name);
    }

    private function getTtl(): ?int
    {
        return ((int) $this->getOption(self::OPTION_TTL)) ?: null;
    }

    /**
     * @throws common_exception_Error
     */
    private function getPersistence(): common_persistence_AdvKeyValuePersistence
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
    private function getKeyPrefix(): string
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
