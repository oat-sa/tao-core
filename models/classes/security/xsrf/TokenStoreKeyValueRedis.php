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

use common_persistence_PhpRedisDriver;
use common_persistence_AdvKeyValuePersistence;

class TokenStoreKeyValueRedis implements TokenStore
{
    private const TOKENS_STORAGE_COLLECTION_KEY_SUFFIX = 'keys';

    private common_persistence_AdvKeyValuePersistence $persistence;
    private common_persistence_PhpRedisDriver $driver;
    private string $keyPrefix;
    private int $ttl;

    public function __construct(
        common_persistence_AdvKeyValuePersistence $persistence,
        common_persistence_PhpRedisDriver $driver,
        string $keyPrefix,
        int $ttl = null
    ) {
        $this->keyPrefix = $keyPrefix;
        $this->persistence = $persistence;
        $this->driver = $driver;
        $this->ttl = $ttl;
    }

    public function getToken(string $tokenId): ?Token
    {
        $tokenData = $this->persistence->get($this->buildKey($tokenId));

        return is_string($tokenData) ? new Token(json_decode($tokenData, true)) : null;
    }

    public function setToken(string $tokenId, Token $token): void
    {
        $tokenKey = $this->buildKey($tokenId);

        $this->persistence->set(
            $tokenKey,
            json_encode($token),
            $this->ttl
        );

        $this->addTokenKeyToCollection($tokenKey);
    }

    public function hasToken(string $tokenId): bool
    {
        return $this->persistence->exists($this->buildKey($tokenId));
    }

    public function removeToken(string $tokenId): bool
    {
        $this->removeTokenKeyFromCollection($tokenId);

        return $this->persistence->del($this->buildKey($tokenId));
    }

    public function clear(): void
    {
        /** @var common_persistence_PhpRedisDriver $driver */
        $driver = $this->persistence->getDriver();

        // @TODO Experimenting with changed on RedisDriver
        // $this->driver->mDel(array_merge($this->getTokenKeys(), [$this->getTokenCollectionKey()]));

        foreach ($this->getTokenKeys() as $tokenKey) {
            $this->persistence->del($tokenKey);
        }

        $this->persistence->del($this->getTokenCollectionKey());
    }

    public function getAll(): array
    {
        $tokens = [];
        // @TODO Experimenting with changed on RedisDriver
//        $res = $this->driver->mGet($this->getTokenKeys());
//
//        if ($res && count($res)) {
//            foreach ($res as $tokenData) {
//                if (is_string($tokenData)) {
//                    $tokens[] = new Token(json_decode($tokenData, true));
//                }
//            }
//        }

        foreach ($this->getTokenKeys() as $key) {
            $tokenData = $this->persistence->get($key);

            if (is_string($tokenData)) {
                $tokens[] = new Token(json_decode($tokenData, true));
            }
        }

        return $tokens;
    }

    private function getTokenCollectionKey(): string
    {
        return $this->buildKey(self::TOKENS_STORAGE_COLLECTION_KEY_SUFFIX);
    }

    private function buildKey(string $name): string
    {
        return sprintf('%s_%s', $this->keyPrefix, $name);
    }

    private function addTokenKeyToCollection(string $tokenKey): bool
    {
        return $this->persistence->set(
            $this->getTokenCollectionKey(),
            json_encode(array_merge($this->getTokenKeys(), [$tokenKey])),
            $this->ttl
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

        $this->persistence->set(
            $this->getTokenCollectionKey(),
            json_encode($collection),
            $this->ttl
        );
    }

    private function getTokenKeys(): array
    {
        return json_decode((string)$this->persistence->get($this->getTokenCollectionKey())) ?? [];
    }
}
