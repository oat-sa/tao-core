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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

namespace oat\tao\model\security\xsrf;

/**
 * Tokens' pool storage
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
interface TokenStore
{
    const TOKEN_NAME = 'XSRF_TOKEN_NAME';

    /**
     * @param string $tokenId
     * @return Token|null
     */
    public function getToken(string $tokenId): ?Token;

    /**
     * @param string $tokenId
     * @param Token $token
     */
    public function setToken(string $tokenId, Token $token): void;

    /**
     * @param string $tokenId
     * @return bool
     */
    public function hasToken(string $tokenId): bool;

    /**
     * @param string $tokenId
     * @return bool
     */
    public function removeToken(string $tokenId): bool;

    /**
     * @return void;
     */
    public function clear(): void;

    /**
     * @return Token[]
     */
    public function getAll(): array;
}
