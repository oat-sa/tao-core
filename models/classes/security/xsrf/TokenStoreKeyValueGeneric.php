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

use common_persistence_KeyValuePersistence;
use common_persistence_AdvKeyValuePersistence;

class TokenStoreKeyValueGeneric implements TokenStore
{
    private common_persistence_KeyValuePersistence $persistence;
    private string $keyPrefix;
    private int $ttl;

    public function __construct(
        common_persistence_AdvKeyValuePersistence $persistence,
        string $keyPrefix,
        int $ttl = null
    ) {
        $this->keyPrefix = $keyPrefix;
        $this->persistence = $persistence;
        $this->ttl = $ttl;
    }

    public function getToken(string $tokenId): ?Token
    {
        if (!$this->hasToken($tokenId)) {
            return null;
        }

        $tokenData = $this->persistence->get($this->buildKey($tokenId));

        return new Token(json_decode($tokenData, true));
    }

    public function setToken(string $tokenId, Token $token): void
    {
        $this->persistence->set(
            $this->buildKey($tokenId),
            json_encode($token),
            $this->ttl
        );
    }

    public function hasToken(string $tokenId): bool
    {
        return $this->persistence->exists($this->buildKey($tokenId));
    }

    public function removeToken(string $tokenId): bool
    {
        if (!$this->hasToken($tokenId)) {
            return false;
        }

        return $this->persistence->del($this->buildKey($tokenId));
    }

    public function clear(): void
    {
        foreach ($this->getKeys() as $key) {
            $this->persistence->del($key);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $tokens = [];

        foreach ($this->getKeys() as $key) {
            $tokenData = $this->persistence->get($key);
            $tokens[] = new Token(json_decode($tokenData, true));
        }

        return $tokens;
    }

    private function getKeys(): array
    {
        $keys = $this->persistence->keys($this->keyPrefix . '*');

        return is_array($keys) ? $keys : [];
    }

    private function buildKey(string $name): string
    {
        return sprintf('%s_%s', $this->keyPrefix, $name);
    }
}
