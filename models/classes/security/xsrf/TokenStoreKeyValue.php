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

    private ?TokenStore $implementation = null;

    public function getToken(string $tokenId): ?Token
    {
        return $this->getImplementation()->getToken($tokenId);
    }

    public function setToken(string $tokenId, Token $token): void
    {
        $this->getImplementation()->setToken($tokenId, $token);
    }

    public function hasToken(string $tokenId): bool
    {
        return $this->getImplementation()->hasToken($tokenId);
    }

    public function removeToken(string $tokenId): bool
    {
        return $this->getImplementation()->removeToken($tokenId);
    }

    public function clear(): void
    {
        $this->getImplementation()->clear();
    }

    public function getAll(): array
    {
        return $this->getImplementation()->getAll();
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

    private function getImplementation(): TokenStore
    {
        if ($this->implementation === null) {
            $persistence = $this->getPersistenceManager()->getPersistenceById(
                $this->getOption(self::OPTION_PERSISTENCE)
            );

            if (!$persistence instanceof common_persistence_AdvKeyValuePersistence) {
                throw new common_exception_Error(
                    'TokenStoreKeyValue expects advanced key value persistence implementation.'
                );
            }

            $driver = $persistence->getDriver();
            $keyPrefix = sprintf(
                '%s_%s',
                //FIXME @Todo remove after tests
                defined('POC_USER_ID') ? POC_USER_ID : $this->getSessionService()->getCurrentUser()->getIdentifier(),
                self::TOKENS_STORAGE_KEY
            );

            //$this->implementation = new TokenStoreKeyValueRedis($persistence, $driver, $keyPrefix, $this->getTtl());
            $this->implementation = new TokenStoreKeyValueGeneric($persistence, $keyPrefix, $this->getTtl());

            //@TODO Uncomment after tests
//            $this->implementation = $driver instanceof common_persistence_PhpRedisDriver
//                ? new TokenStoreKeyValueRedis($persistence, $driver, $keyPrefix, $this->getTtl())
//                : new TokenStoreKeyValueGeneric($persistence, $keyPrefix, $this->getTtl());
        }

        return $this->implementation;
    }
}
